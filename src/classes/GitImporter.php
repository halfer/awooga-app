<?php

namespace Awooga;

class GitImporter
{
	const LOG_TYPE_FETCH = 'fetch';
	const LOG_TYPE_MOVE = 'move';
	const LOG_TYPE_SCAN = 'scan';
	const LOG_TYPE_RESCHED = 'resched';

	protected $pdo;
	protected $repoRoot;
	protected $debug;

	public function __construct(\PDO $pdo, $repoRoot, $debug = false)
	{
		$this->pdo = $pdo;
		$this->repoRoot = $repoRoot;
		$this->debug = $debug;
	}

	public function processRepo($repoId, $url, $oldPath)
	{
		// Try a new clone
		try
		{
			$newPath = $this->doClone($url);
			$this->repoLog($repoId, self::LOG_TYPE_FETCH);
		}
		catch (\Exception $e)
		{
			$this->repoLog($repoId, self::LOG_TYPE_FETCH, 'Fetch failed', false);
			return false;
		}

		// Try moving the clone into place
		try
		{
			$this->moveRepoLocation($repoId, $oldPath, $newPath);
			$this->repoLog($repoId, self::LOG_TYPE_MOVE);
		}
		catch (\Exception $e)
		{
			$this->repoLog($repoId, self::LOG_TYPE_MOVE, "Move from $oldPath to $newPath failed", false);
			return false;
		}

		// Scan repo
		try
		{
			$this->pdo->beginTransaction();
			$this->scanRepo($repoId, $newPath);
			$this->pdo->commit();
			$this->repoLog($repoId, self::LOG_TYPE_SCAN);
		}
		catch (\Exception $e)
		{
			$this->pdo->rollBack();
			// @todo Catch a specific exception for which we can save messages into the public log safely
			$this->repoLog($repoId, self::LOG_TYPE_SCAN, "Scanning failure", false);
			return false;
		}

		// Reschedule another scan
		try
		{
			$this->rescheduleRepo($repoId);
			$this->repoLog($repoId, self::LOG_TYPE_RESCHED);
		}
		catch (\Exception $e)
		{
			// @todo Catch a specific exception for which we can save messages into the public log safely
			$this->repoLog($repoId, self::LOG_TYPE_RESCHED, "Failed to reschedule repo", false);
		}

		return true;
	}

	public function doClone($url)
	{
		// Create new checkout path
		$target = sha1($url . rand(1, 99999) . time());

		// Turn relative target into fully qualified path
		$fqTarget = $this->repoRoot . '/' . $target;

		// Emptying HOME is to prevent Git trying to fetch config it doesn't have access to
		$command = "HOME='' git clone --quiet \\
			{$url} \\
			{$fqTarget}";
		$output = $return = null;
		exec($command, $output, $return);

		if ($return)
		{
			throw new Exceptions\SeriousException("Problem when cloning");
		}

		$this->writeDebug("System command: $command");

		return $target;
	}

	public function moveRepoLocation($repoId, $oldPath, $newPath)
	{
		// Update the row with the new location
		$sql = "
			UPDATE repository SET mount_path = :path WHERE id = :id
		";
		$statement = $this->pdo->prepare($sql);
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
			$output = $return = null;
			$command = "rm -rf {$this->repoRoot}/{$oldPath}";
			exec($command, $output, $return);

			if ($return)
			{
				throw new Exceptions\SeriousException("Problem when deleting the old repo");
			}

			$this->writeDebug("Remove old location '{$oldPath}' for repo #{$repoId}");
		}
	}

	/**
	 * Scans a folder for JSON reports
	 * 
	 * @todo Need to have a file size filter here - anything over 256K?
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
		foreach ($regex as $file)
		{
			$reportPath = $file[0];
			try
			{
				$this->scanReport($repoId, $reportPath);
				$this->writeDebug("\tFound report ..." . substr($reportPath, -80));
			}
			catch (Exceptions\TrivialException $e)
			{
				// Counting trivial exceptions still contributes to failure/stop limit
				$this->repoLog($repoId, self::LOG_TYPE_SCAN, $e->getMessage(), false);
				$this->doesErrorCountRequireHalting($repoId);
			}
			// For serious/other exceptions, rethrow
			catch (\Exception $e)
			{
				throw $e;
			}
		}
	}

	/**
	 * Scans a single report and commits it to the database
	 * 
	 * @todo Review the JSON recursion limit, is this OK?
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
				$report->save();
				break;
			default:
				throw new Exceptions\TrivialException("Unrecognised version number");
		}
	}

	/**
	 * 
	 * @param integer $repoId
	 */
	protected function doesErrorCountRequireHalting($repoId)
	{
		// @todo if there are 5 errors recently, throw Exceptions\SeriousException
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

	protected function rescheduleRepo($repoId)
	{
		// @todo
	}

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
			(repository_id, log_type, message, created_at, is_success)
			VALUES
			(:repository_id, :log_type, :message, NOW(), :is_success)
		";
		$statement = $this->pdo->prepare($sql);
		$ok = $statement->execute(
			array(
				':repository_id' => $repoId, ':log_type' => $logType,
				':message' => $message, ':is_success' => $isSuccess,
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