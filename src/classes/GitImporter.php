<?php

namespace Awooga;

class GitImporter
{
	const LOG_TYPE_FETCH = 'fetch';
	const LOG_TYPE_MOVE = 'move';
	const LOG_TYPE_SCAN = 'scan';
	const LOG_TYPE_RESCHED = 'resched';

	const MAX_FAILS_BEFORE_DISABLE = 5;
	const MAX_REPORT_SIZE = 60000;

	protected $runId;
	protected $repoRoot;
	protected $debug;

	use Database;

	/**
	 * Constructs an importer object
	 * 
	 * @todo Repo ID should be a class-wide property
	 * 
	 * @param integer $runId
	 * @param string $repoRoot
	 * @param \PDO $pdo
	 * @param boolean $debug
	 */
	public function __construct($runId, $repoRoot, $debug = false)
	{
		$this->runId = $runId;
		$this->repoRoot = $repoRoot;
		$this->debug = $debug;
	}

	/**
	 * Calls the various parts of an import process
	 * 
	 * I need to think about how to reschedule on failure (presumably I don't want to retry
	 * straight away?). Also if there is a large number of failures, let's disable the repo,
	 * like in the scanRepo method.
	 * 
	 * @param integer $repoId
	 * @param string $url
	 * @param string $oldPath
	 * @return boolean
	 */
	public function processRepo($repoId, $url, $oldPath)
	{
		// If any part fails, the following will (deliberately) not be called
		$ok =
			($newPath = $this->cloneRepoWithLogging($repoId, $url)) &&
			$this->moveRepoWithLogging($repoId, $oldPath, $newPath) &&
			$this->scanRepoWithLogging($repoId, $newPath);

		// We always reschedule
		$rescheduleOk = $this->rescheduleRepoWithLogging($repoId, $ok);

		return $ok;
	}

	/**
	 * Tries to clone the specified repo, and logs the success/failure
	 * 
	 * @param integer $repoId
	 * @param string $url
	 * @return boolean
	 */
	public function cloneRepoWithLogging($repoId, $url)
	{
		try
		{
			$newPath = $this->cloneRepo($url);
			$this->repoLog($repoId, self::LOG_TYPE_FETCH);
		}
		catch (\Exception $e)
		{
			$this->repoLog($repoId, self::LOG_TYPE_FETCH, 'Fetch failed', false);

			return false;
		}

		return $newPath;
	}

	/**
	 * Tries to move a repository, and logs the success/failure
	 * 
	 * @param integer $repoId
	 * @param string $oldPath
	 * @param string $newPath
	 * @return boolean
	 */
	public function moveRepoWithLogging($repoId, $oldPath, $newPath)
	{
		try
		{
			$this->moveRepo($repoId, $oldPath, $newPath);
			$this->repoLog($repoId, self::LOG_TYPE_MOVE);
		}
		catch (\Exception $e)
		{
			$this->repoLog($repoId, self::LOG_TYPE_MOVE, "Move from $oldPath to $newPath failed", false);

			return false;
		}

		return true;
	}

	/**
	 * Tries to scan a repository, and logs the success/failure
	 * 
	 * @param integer $repoId
	 * @param string $repoPath
	 * @return boolean
	 */
	public function scanRepoWithLogging($repoId, $repoPath)
	{
		$pdo = $this->getDriver();
		$exitEarly = false;
		try
		{
			$pdo->beginTransaction();
			$this->scanRepo($repoId, $repoPath);
			$pdo->commit();
			$this->repoLog($repoId, self::LOG_TYPE_SCAN);
		}
		catch (Exceptions\SeriousException $e)
		{
			// We'll already have logged, so no need to do it again
			$exitEarly = true;
		}
		catch (\Exception $e)
		{
			// Let's not add these to the public log
			$this->repoLog($repoId, self::LOG_TYPE_SCAN, "Scanning failure", false);

			$exitEarly = true;
		}

		// A common handler for exiting early
		if ($exitEarly)
		{
			$pdo->rollBack();
			return false;
		}

		return true;
	}

	/**
	 * Tries to reschedule a repository, with success/failure logging
	 * 
	 * @param integer $repoId
	 * @param boolean $wasSuccessful
	 * @return boolean
	 */
	public function rescheduleRepoWithLogging($repoId, $wasSuccessful)
	{
		// Add in suitable message
		$message = $wasSuccessful ? null : 'Repo processing failed, setting retry time';

		try
		{
			$this->rescheduleRepo($repoId, $wasSuccessful);
			$this->repoLog($repoId, self::LOG_TYPE_RESCHED, $message);
		}
		catch (\Exception $e)
		{
			// @todo Catch a specific exception for which we can save messages into the public log safely
			$this->repoLog($repoId, self::LOG_TYPE_RESCHED, "Failed to reschedule repo", false);

			return false;
		}

		return true;
	}

	/**
	 * Clones the repo
	 * 
	 * @todo Does this need to be public?
	 * 
	 * @param string $url
	 * @return string
	 * @throws Exceptions\SeriousException
	 */
	public function cloneRepo($url)
	{
		// Create new checkout path
		$target = $this->getCheckoutPath($url);

		// Turn relative target into fully qualified path
		$fqTarget = $this->repoRoot . '/' . $target;
		$ok = $this->runGitCommand($url, $fqTarget);

		if (!$ok)
		{
			throw new Exceptions\SeriousException("Problem when cloning");
		}

		return $target;
	}

	/**
	 * Returns relative checkout path
	 * 
	 * @param string $url
	 * @return string
	 */
	protected function getCheckoutPath($url)
	{
		return sha1($url . rand(1, 99999) . time());
	}

	/**
	 * Clones the repo at the URL into the file system path
	 * 
	 * Emptying HOME is to prevent Git trying to fetch config it doesn't have access to
	 * 
	 * @param string $url
	 * @param string $path
	 * @return boolean True on successful clone
	 */
	protected function runGitCommand($url, $path)
	{
		$command = "HOME='' git clone --quiet \\
			{$url} \\
			{$path}";
		$output = $return = null;
		exec($command, $output, $return);

		// Write debug to screen if required
		$this->writeDebug("System command: $command");

		return $return === 0;
	}

	/**
	 * Updates the location and deletes the old one if necessary
	 *
	 * @todo This would be better if it deleted the repo referenced in the database.
	 * We'd then not need the $oldPath parameter at all, presumably.
	 *  
	 * @todo Does this need to be public?
	 * 
	 * @param integer $repoId
	 * @param string $oldPath
	 * @param string $newPath
	 * @throws Exceptions\SeriousException
	 */
	public function moveRepo($repoId, $oldPath, $newPath)
	{
		// Update the row with the new location
		$sql = "
			UPDATE repository SET mount_path = :path WHERE id = :id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':path' => $newPath, ':id' => $repoId, ));

		// Let's bork if the query failed
		if (!$ok)
		{
			throw new Exceptions\SeriousException("Updating the repo path failed");
		}

		$this->writeDebug("Update path '{$newPath}' for repo #{$repoId}");

		// Delete the old location if there is one
		if ($oldPath)
		{
			// Halt if there's no root, to avoid a dangerous command :)
			if (!$this->repoRoot)
			{
				throw new Exceptions\SeriousException(
					"No repository root set, cannot delete old repo"
				);
			}

			$ok = $this->deleteOldRepo($oldPath);
			if (!$ok)
			{
				throw new Exceptions\SeriousException("Problem when deleting the old repo");
			}

			$this->writeDebug("Remove old location '{$oldPath}' for repo #{$repoId}");
		}
	}

	/**
	 * Deletes a folder from the filing system
	 * 
	 * @todo Move the protection clause in moveRepo() to this method?
	 * 
	 * @param string $oldPath
	 * @return boolean Success
	 */
	protected function deleteOldRepo($oldPath)
	{
		$output = $return = null;
		$command = "rm -rf {$this->repoRoot}/{$oldPath}";
		exec($command, $output, $return);

		return $return === 0;
	}

	/**
	 * Updates the database copy of this repo's relative path
	 * 
	 * @todo Move the query in moveRepoLocation here? Useful elsewhere.
	 * 
	 * @param integer $repoId
	 * @param string $repoPath
	 */
	protected function updateRepoLocation($repoId, $repoPath)
	{
		
	}

	/**
	 * Scans a folder for JSON reports
	 * 
	 * @param integer $repoId
	 * @param string $repoPath
	 * @throws Exception
	 */
	protected function scanRepo($repoId, $repoPath)
	{
		// Set up iterator to find JSON files
		$directory = new \RecursiveDirectoryIterator($this->repoRoot . '/' . $repoPath);
		$iterator = new \RecursiveIteratorIterator($directory);
		$regex = new \RegexIterator($iterator, '/^.+\.json$/i', \RecursiveRegexIterator::GET_MATCH);

		$this->writeDebug("Finding files in repo:");

		// Keep a log of reports we create/update
		$reportIds = array();

		try
		{
			foreach ($regex as $file)
			{
				$reportPath = $file[0];
				try
				{
					$reportIds[] = $this->scanReport($repoId, $reportPath);
					$this->writeDebug("\tFound report ..." . substr($reportPath, -80));
				}
				catch (Exceptions\TrivialException $e)
				{
					// Counting trivial exceptions still contributes to failure/stop limit
					$this->repoLog($repoId, self::LOG_TYPE_SCAN, $e->getMessage(), false);
					$this->doesErrorCountRequireHalting($repoId);
				}
				// For serious/other exceptions, rethrow to outer catch
				catch (\Exception $e)
				{
					throw $e;
				}
			}
		}
		catch (Exceptions\SeriousException $e)
		{
			// These errors are always OK to save directly into the log
			$this->repoLog($repoId, self::LOG_TYPE_SCAN, $e->getMessage(), false);
			$this->disableRepo($repoId);

			// Rethrow for benefit of caller
			throw $e;
		}

		return $reportIds;
	}

	/**
	 * Scans a single report and commits it to the database
	 * 
	 * Review the JSON recursion limit, is this OK?
	 * 
	 * @param integer $repoId
	 * @param string $reportPath
	 * @throws Exception
	 */
	protected function scanReport($repoId, $reportPath)
	{
		// Unlikely to happen, we just scanned!
		if (!file_exists($reportPath))
		{
			throw new Exceptions\SeriousException('File cannot be found');
		}

		$size = filesize($reportPath);
		if ($size > self::MAX_REPORT_SIZE)
		{
			throw new Exceptions\FileException('Report of ' . $size . ' bytes is too large');
		}

		// Let's get this in array form
		$data = json_decode(file_get_contents($reportPath), true, 4);

		// If this is not an array, throw a trivial exception
		if (!is_array($data))
		{
			throw new Exceptions\TrivialException("Could not parse report into an array");
		}

		// Parse the data
		$version = $this->grabElement($data, 'version');
		$title = $this->grabElement($data, 'title');
		$url = $this->grabElement($data, 'url');
		$description = $this->grabElement($data, 'description');
		$issues = $this->grabElement($data, 'issues');
		$notifiedDate = $this->grabElement($data, 'author_notified_date');

		// Handle depending on version
		switch ($version)
		{
			case 1:
				$report = new Report($repoId);
				$report->setDriver($this->pdo);
				$report->setTitle($title);
				$report->setUrl($url);
				$report->setDescription($description);
				$report->setIssues($issues);
				$report->setAuthorNotifiedDate($notifiedDate);
				$reportId = $report->save();
				break;
			default:
				throw new Exceptions\TrivialException("Unrecognised version number");
		}

		return $reportId;
	}

	/**
	 * Bomb out if there's been too many errors recently
	 * 
	 * @todo Rather than the 4 hour window, use a run table for this
	 * 
	 * @param integer $repoId
	 */
	protected function doesErrorCountRequireHalting($repoId)
	{
		// If there are too many errors recently, throw Exceptions\SeriousException
		$sql = "
			SELECT COUNT(*) count
			FROM repository_log
			WHERE
				repository_id = :repo_id
				AND is_success = false
				AND created_at > (DATE_SUB(CURDATE(), INTERVAL 4 HOUR))
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':repo_id' => $repoId, ));

		if ($statement->fetchColumn() > self::MAX_FAILS_BEFORE_DISABLE)
		{
			throw new Exceptions\SeriousException(
				"Too many failures with this repo recently, please see log"
			);
		}
	}

	/**
	 * Disables the specified repo
	 * 
	 * @param integer $repoId
	 */
	protected function disableRepo($repoId)
	{
		$sql = "
			UPDATE repository
			SET is_enabled = false
				WHERE id = :repo_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':repo_id' => $repoId, ));
		if (!$ok)
		{
			throw new \Exception('Failed disabling this repo');
		}

		return $ok;
	}

	/**
	 * Grabs a keyed value from a hash
	 * 
	 * @param array $data
	 * @param string $key
	 * @return mixed
	 */
	protected function grabElement(array $data, $key)
	{
		return isset($data[$key]) ? $data[$key] : null;
	}

	/**
	 * Readies this repo to be pulled in four hours from now
	 * 
	 * Maybe this should be configurable?
	 * 
	 * @todo For failures, let's count the number of recent failed runs, and increase the sched time
	 * 
	 * @param integer $repoId
	 * @param boolean $wasSuccessful
	 * @return True if successful
	 */
	protected function rescheduleRepo($repoId, $wasSuccessful)
	{
		$sql = "
			UPDATE repository
				SET due_at = NOW() + INTERVAL :time_hours HOUR
				WHERE id = :repo_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repo_id' => $repoId,
				':time_hours' => $wasSuccessful ? 4 : 1,
			)
		);

		return $ok;
	}

	/**
	 * @todo Finish me
	 * 
	 * @param integer $repoId
	 */
	protected function countRecentFails($repoId)
	{
		/*
		 * Need SQL to return 0 per run per repo if anything failed. Or we could just scan in PHP,
		 * much easier!
		 * 
		 * Also, we don't want to look at scan items - individual fails there are fine. If we
		 * log those as trivial errors and others as serious, that might work out
		 */
		$sql = "
			SELECT * FROM repository_log
			WHERE
				repository_id = :repo_id
			ORDER BY
				run_id DESC,
				id DESC
			LIMIT
				10
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array('repo_id' => $repoId, )
		);

		// Loop through to count fails
		while ($statement->fetch(\PDO::FETCH_ASSOC))
		{
			
		}
	}

	/**
	 * Logs a message against a repo
	 * 
	 * @todo Does this need to be public?
	 * 
	 * @param integer $repoId
	 * @param string $logType
	 * @param string $message
	 * @param boolean $isSuccess
	 * @throws \Exception
	 */
	public function repoLog($repoId, $logType, $message = null, $isSuccess = true)
	{
		// Check the type is OK
		$allowedTypes = array(
			self::LOG_TYPE_FETCH,
			self::LOG_TYPE_MOVE,
			self::LOG_TYPE_SCAN,
			self::LOG_TYPE_RESCHED,
		);
		if (!in_array($logType, $allowedTypes))
		{
			throw new \Exception("The supplied type is not valid");
		}

		$sql = "
			INSERT INTO repository_log
			(repository_id, run_id, log_type, message, created_at, is_success)
			VALUES
			(:repository_id, :run_id, :log_type, :message, NOW(), :is_success)
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repository_id' => $repoId, ':run_id' => $this->runId,
				':log_type' => $logType, ':message' => $message, ':is_success' => $isSuccess,
			)
		);
		if (!$ok)
		{
			throw new \Exception('Adding a log message seems to have failed');
		}

		$successType = $isSuccess ? 'success' : 'failure';
		$this->writeDebug("Adding {$successType} log message for '{$logType}' op");
	}

	protected function writeDebug($message)
	{
		if ($this->debug)
		{
			echo $message . "\n";
		}
	}
}
