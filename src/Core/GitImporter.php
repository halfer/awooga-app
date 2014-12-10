<?php

namespace Awooga\Core;

class GitImporter
{
	const LOG_TYPE_FETCH = 'fetch';
	const LOG_TYPE_MOVE = 'move';
	const LOG_TYPE_SCAN = 'scan';
	const LOG_TYPE_RESCHED = 'resched';

	const LOG_LEVEL_SUCCESS = 'success';
	const LOG_LEVEL_ERROR_TRIVIAL = 'trivial';
	const LOG_LEVEL_ERROR_SERIOUS = 'serious';

	// @todo Is this misnamed? Currently only throws exception, doesn't disable
	const MAX_FAILS_BEFORE_DISABLE = 5;
	const MAX_REPORT_SIZE = 60000;

	protected $runId;
	protected $repoRoot;
	protected $searcher;
	protected $debug;

	use Database;

	/**
	 * Constructs an importer object
	 * 
	 * I removed the run from the constructor, since we usually rely on UpdateAll to set it.
	 * 
	 * @todo Repo ID should be a class-wide property
	 * @todo Should we throw exception if repoRoot is null/empty?
	 * 
	 * @param string $repoRoot
	 * @param integer $runId
	 * @param \PDO $pdo
	 * @param boolean $debug
	 */
	public function __construct($repoRoot, $debug = false)
	{
		$this->repoRoot = $repoRoot;
		$this->debug = $debug;
	}

	/**
	 * Calls the various parts of an import process
	 * 
	 * @todo Currently we aren't disabling the repo - what conditions should trigger that?
	 * 
	 * @param integer $repoId
	 * @param string $url
	 * @return boolean
	 */
	public function processRepo($repoId, $url)
	{
		// If any part fails, the following will (deliberately) not be called
		$ok =
			($newPath = $this->cloneRepoWithLogging($repoId, $url)) &&
			$this->moveRepoWithLogging($repoId, $newPath) &&
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
			$this->repoLog(
				$repoId,
				self::LOG_TYPE_FETCH,
				'Fetch failed',
				self::LOG_LEVEL_ERROR_SERIOUS
			);

			return false;
		}

		return $newPath;
	}

	/**
	 * Tries to move a repository, and logs the success/failure
	 * 
	 * @param integer $repoId
	 * @param string $newPath
	 * @return boolean
	 */
	public function moveRepoWithLogging($repoId, $newPath)
	{
		try
		{
			$this->moveRepo($repoId, $newPath);
			$this->repoLog($repoId, self::LOG_TYPE_MOVE);
		}
		catch (\Exception $e)
		{
			$this->repoLog(
				$repoId,
				self::LOG_TYPE_MOVE,
				"Move repo to {$newPath} failed",
				self::LOG_LEVEL_ERROR_SERIOUS
			);

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
		catch (\Awooga\Exceptions\SeriousException $e)
		{
			// We'll already have logged, so no need to do it again
			$exitEarly = true;
		}
		catch (\Exception $e)
		{
			// Let's not add these to the public log
			$this->repoLog(
				$repoId,
				self::LOG_TYPE_SCAN,
				"Scanning failure",
				self::LOG_LEVEL_ERROR_SERIOUS
			);

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
			$this->repoLog(
				$repoId,
				self::LOG_TYPE_RESCHED,
				"Failed to reschedule repo",
				self::LOG_LEVEL_ERROR_SERIOUS
			);

			return false;
		}

		return true;
	}

	/**
	 * Sets the current run ID
	 * 
	 * @param integer $runId
	 */
	public function setRunId($runId)
	{
		$this->runId = $runId;
	}

	/**
	 * Clones the repo
	 * 
	 * @param string $url
	 * @return string
	 * @throws \Awooga\Exceptions\SeriousException
	 */
	protected function cloneRepo($url)
	{
		// Create new checkout path
		$target = $this->getCheckoutPath($url);

		// Turn relative target into fully qualified path
		$fqTarget = $this->repoRoot . '/' . $target;
		$ok = $this->runGitCommand($url, $fqTarget);

		if (!$ok)
		{
			throw new \Awooga\Exceptions\SeriousException("Problem when cloning");
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
	 * Emptying HOME is to prevent Git trying to fetch config it doesn't have access to. Also
	 * the Git clone command is fussy, it tries to access things like /root if the current
	 * working directory isn't initially set to something that is writeable.
	 * 
	 * @param string $url
	 * @param string $path
	 * @return boolean True on successful clone
	 */
	protected function runGitCommand($url, $path)
	{
		$dir = dirname($path);
		$command = "cd $dir; \\
			HOME='' git clone --quiet \\
			{$url} \\
			{$path} 2>&1";
		$output = $return = null;
		exec($command, $output, $return);

		// Write debug to screen if required
		$this->writeDebug("System command: $command");

		return $return === 0;
	}

	/**
	 * Updates the location and deletes the old one if necessary
	 *
	 * @param integer $repoId
	 * @param string $newPath
	 * @throws \Awooga\Exceptions\SeriousException
	 */
	protected function moveRepo($repoId, $newPath)
	{
		// Get the old path
		$statementRead = $this->getDriver()->prepare(
			$sql = "SELECT mount_path FROM repository WHERE id = :repo_id"
		);
		$okRead = $statementRead->execute(array(':repo_id' => $repoId, ));
		$oldPath = $statementRead->fetchColumn();

		// Update the row with the new location
		$okWrite = $this->updateMountPath($repoId, $newPath);

		// Let's bork if either of the queries failed
		if (!$okRead || !$okWrite)
		{
			throw new \Awooga\Exceptions\SeriousException("Updating the repo path failed");
		}

		$this->writeDebug("Update path '{$newPath}' for repo #{$repoId}");

		// Delete the old location if there is one
		if ($oldPath)
		{
			if (!$this->deleteOldRepo($oldPath))
			{
				throw new \Awooga\Exceptions\SeriousException("Problem when deleting the old repo");
			}

			$this->writeDebug("Remove old location '{$oldPath}' for repo #{$repoId}");
		}
	}

	/**
	 * Updates the relative mount point in the database
	 * 
	 * @todo Can this be made protected, and add a public accesor in the test harness?
	 * 
	 * @param integer $repoId
	 * @param string $newPath
	 * @return boolean
	 */
	public function updateMountPath($repoId, $newPath)
	{
		$statement = $this->getDriver()->prepare(
			"UPDATE repository SET mount_path = :path WHERE id = :repo_id"
		);

		return $statement->execute(array(':path' => $newPath, ':repo_id' => $repoId, ));		
	}

	/**
	 * Deletes a folder from the filing system
	 * 
	 * @param string $oldPath
	 * @return boolean True on success
	 * @throws \Awooga\Exceptions\SeriousException
	 */
	protected function deleteOldRepo($oldPath)
	{
		// Halt if there's no root, to avoid a dangerous command :)
		if (!$this->repoRoot)
		{
			throw new \Awooga\Exceptions\SeriousException(
				"No repository root set, cannot delete old repo"
			);
		}

		$output = $return = null;
		$command = "rm -rf {$this->repoRoot}/{$oldPath}";
		exec($command, $output, $return);

		// Write debug to screen if required
		$this->writeDebug("System command: $command");

		return $return === 0;
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
				catch (\Awooga\Exceptions\TrivialException $e)
				{
					// Counting trivial exceptions still contributes to failure/stop limit
					$this->repoLog($repoId, self::LOG_TYPE_SCAN, $e->getMessage(), self::LOG_LEVEL_ERROR_TRIVIAL);
					$this->doesErrorCountRequireHalting($repoId);
				}
				// For serious/other exceptions, rethrow to outer catch
				catch (\Exception $e)
				{
					throw $e;
				}
			}
		}
		catch (\Awooga\Exceptions\SeriousException $e)
		{
			// These errors are always OK to save directly into the log
			$this->repoLog($repoId, self::LOG_TYPE_SCAN, $e->getMessage(), self::LOG_LEVEL_ERROR_SERIOUS);
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
			throw new \Awooga\Exceptions\SeriousException('File cannot be found');
		}

		$size = filesize($reportPath);
		if ($size > self::MAX_REPORT_SIZE)
		{
			throw new \Awooga\Exceptions\FileException('Report of ' . $size . ' bytes is too large');
		}

		// Let's get this in array form
		$data = json_decode(file_get_contents($reportPath), true, 4);

		// If this is not an array, throw a trivial exception
		if (!is_array($data))
		{
			throw new \Awooga\Exceptions\TrivialException("Could not parse report into an array");
		}

		// Parse the data
		$version = $this->grabElement($data, 'version');
		$url = $this->grabElement($data, 'url');
		$issues = $this->grabElement($data, 'issues');
		// @todo Move these back to var names, or better still move them to setters directly
		$reportData = array(
			'title' => $this->grabElement($data, 'title'),
			'description' => $this->grabElement($data, 'description'),
			'notified_date' => $this->grabElement($data, 'author_notified_date'),
		);

		// Handle depending on version
		switch ($version)
		{
			case 1:
				$report = new Report($repoId);
				$report->setDriver($this->pdo);
				$report->setTitle($reportData['title']);
				$report->setUrl($url);
				$report->setDescription($reportData['description']);
				$report->setIssues($issues);
				$report->setAuthorNotifiedDate($reportData['notified_date']);
				$reportId = $report->save();
				
				// This will only be called if the above does not throw an exception
				$this->tryReindexing($report);
				break;
			default:
				throw new \Awooga\Exceptions\TrivialException("Unrecognised version number");
		}

		return $reportId;
	}

	/**
	 * Bomb out if there's been too many errors recently
	 * 
	 * @param integer $repoId
	 */
	protected function doesErrorCountRequireHalting($repoId)
	{
		// If there are too many errors in this run, throw Exceptions\SeriousException
		$sql = "
			SELECT COUNT(*) count
			FROM repository_log
			WHERE
				repository_id = :repo_id
				AND run_id = :run_id
				AND log_level = :level_trivial
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repo_id' => $repoId,
				':run_id' => $this->runId,
				':level_trivial' => self::LOG_LEVEL_ERROR_TRIVIAL,
			)
		);

		// Make sure the query went ok
		if (!$ok)
		{
			throw new \Exception('Query to count trivial errors did not run');
		}

		if ($statement->fetchColumn() > self::MAX_FAILS_BEFORE_DISABLE)
		{
			throw new \Awooga\Exceptions\SeriousException(
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
	 * @param integer $repoId
	 * @param boolean $wasSuccessful
	 * @return boolean True if successful
	 */
	protected function rescheduleRepo($repoId, $wasSuccessful)
	{
		$minutes = $wasSuccessful ? 4 * 60 : $this->getRetryInMinutes($repoId);
		$sql = "
			UPDATE repository
				SET due_at = NOW() + INTERVAL :time_minutes MINUTE
				WHERE id = :repo_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repo_id' => $repoId,
				':time_minutes' => $minutes,
			)
		);

		return $ok;
	}

	/**
	 * Gives an exponential retry curve for persistent serious failures
	 * 
	 * @param integer $repoId
	 * @return integer
	 */
	protected function getRetryInMinutes($repoId)
	{
		$fails = $this->countRecentFails($repoId);

		return (int) pow($fails * 3, 2.3) * 3;
	}

	/**
	 * Counts the number of recent consectuve serious failures in the logs
	 * 
	 * @param integer $repoId
	 */
	protected function countRecentFails($repoId)
	{
		// This clause gets the number of serious fails per run
		$sql = "
			SELECT
				run_id,
				(
					SELECT COUNT(*)
					FROM repository_log l
					WHERE
						l.log_level = :log_level
						AND l.run_id = repository_log.run_id
						AND l.repository_id = repository_log.repository_id
				) serious_count
			FROM
				repository_log
			WHERE
				repository_id = :repo_id
			GROUP BY
				run_id
			ORDER BY
				run_id DESC
			LIMIT
				10
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(':repo_id' => $repoId, ':log_level' => self::LOG_LEVEL_ERROR_SERIOUS, )
		);
		if (!$ok)
		{
			throw new \Exception("Could not run the error count query");
		}

		// Loop through to count fails
		$failCount = 0;
		while ($row = $statement->fetch(\PDO::FETCH_ASSOC))
		{
			$thisFails = $row['serious_count'];
			if ($thisFails)
			{
				$failCount++;
			}
			else
			{
				// As soon as we find a run with no serious errors, it's not contiguous
				break;
			}
		}

		return $failCount;
	}

	protected function tryReindexing(Report $report)
	{
		if ($searcher = $this->getSearcher())
		{
			$report->index($searcher);
		}
	}

	/**
	 * Gets the currently set searcher
	 * 
	 * Fails silently if no searcher has been set
	 * 
	 * @return Searcher
	 */
	public function getSearcher()
	{
		return $this->searcher;
	}

	public function setSearcher(Searcher $searcher)
	{
		$this->searcher = $searcher;
	}

	/**
	 * Logs a message against a repo
	 * 
	 * @param integer $repoId
	 * @param string $logType
	 * @param string $message
	 * @param string $logLevel
	 * @throws \Exception
	 */
	protected function repoLog($repoId, $logType, $message = null, $logLevel = self::LOG_LEVEL_SUCCESS)
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
			(repository_id, run_id, log_type, message, created_at, log_level)
			VALUES
			(:repository_id, :run_id, :log_type, :message, NOW(), :log_level)
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repository_id' => $repoId, ':run_id' => $this->runId,
				':log_type' => $logType, ':message' => $message, ':log_level' => $logLevel,
			)
		);
		if (!$ok)
		{
			throw new \Exception('Adding a log message seems to have failed');
		}

		$successType = ($logLevel == self::LOG_LEVEL_SUCCESS) ? 'success' : 'failure';
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
