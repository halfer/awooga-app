<?php

namespace Awooga\Core;

require_once 'BaseGitImporter.php';

use \Awooga\Exceptions\SeriousException;

class GitImporter extends BaseGitImporter
{
	protected $searcher;

	use \Awooga\Traits\Runner;

	/**
	 * Constructs an importer object
	 * 
	 * I removed the run from the constructor, since we usually rely on UpdateAll to set it.
	 * 
	 * @todo Repo ID should be a class-wide property
	 * @todo Should we throw exception if repoRoot is null/empty?
	 * 
	 * @param string $repoRoot
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
		$this->rescheduleRepoWithLogging($repoId, $ok);

		// @todo Can we make this $newPath on success or false on failure?
		return $ok;
	}

	/**
	 * Tries to clone the specified repo, and logs the success/failure
	 * 
	 * @param integer $repoId
	 * @param string $url
	 * @return false|string
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
			// SeriousExceptions are safe enough to put into the public log system
			$this->repoLog(
				$repoId,
				self::LOG_TYPE_FETCH,
				$e instanceof SeriousException ? $e->getMessage() : 'Fetch failed',
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
		// Initialisation
		$pdo = $this->getDriver();
		$scanner = $this->getScanner($repoId, $this->repoRoot);
		$scanner->setDriver($pdo);
		if ($this->searcher)
		{
			$scanner->setSearcher($this->searcher);
		}

		$exitEarly = false;
		try
		{
			$pdo->beginTransaction();
			$scanner->scanRepo($repoPath);
			$pdo->commit();
			$this->repoLog($repoId, self::LOG_TYPE_SCAN);
		}
		catch (SeriousException $e)
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
	 * An easily overridable class to get a new scanning object
	 * 
	 * @param integer $repoId
	 * @param string $repoRoot
	 * @return GitScanner
	 */
	protected function getScanner($repoId, $repoRoot)
	{
		return new GitScanner($this->runId, $repoId, $repoRoot);
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
	 * @throws SeriousException
	 */
	protected function cloneRepo($url)
	{
		// Create new checkout path
		$target = $this->getCheckoutPath($url);
		$fqTarget = $this->repoRoot . '/' . $target;

		// Let's do an explicit check for permissions, so we can report it properly
		$writeable = @mkdir($fqTarget, 0777, true) && @rmdir($fqTarget);
		if (!$writeable)
		{
			$relativePath = str_replace($this->repoRoot, '', $fqTarget);
			$this->writeDebug(
				$error = "A new repo directory '{$relativePath}' could not be created, permission error?"
			);
			throw new SeriousException($error);
		}

		$ok = $this->runGitCommand($url, $fqTarget);

		if (!$ok)
		{
			throw new SeriousException("Problem when cloning from Git repository");
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
		$this->writeDebug($return === 0 ? 'Call successful.' : 'Call failed.');
		if ($output)
		{
			$this->writeDebug("Output: " . implode("\n", $output));
		}

		return $return === 0;
	}

	/**
	 * Updates the location and deletes the old one if necessary
	 *
	 * @param integer $repoId
	 * @param string $newPath
	 * @throws SeriousException
	 */
	protected function moveRepo($repoId, $newPath)
	{
		// Get the old path
		$oldPath = $this->fetchColumn(
			$this->getDriver(),
			"SELECT mount_path FROM repository WHERE id = :repo_id",
			array(':repo_id' => $repoId, )
		);

		// Update the row with the new location
		$okWrite = $this->updateMountPath($repoId, $newPath);

		// Let's bork if the mount failed
		if (!$okWrite)
		{
			throw new SeriousException("Updating the repo path failed");
		}

		$this->writeDebug("Update path '{$newPath}' for repo #{$repoId}");

		// Delete the old location if there is one
		if ($oldPath)
		{
			if (!$this->deleteOldRepo($oldPath))
			{
				throw new SeriousException("Problem when deleting the old repo");
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
		return $this->runStatement(
			$this->getDriver(),
			"UPDATE repository SET mount_path = :path WHERE id = :repo_id",
			array(':path' => $newPath, ':repo_id' => $repoId, )
		);
	}

	/**
	 * Readies this repo to be pulled in four hours from now
	 * 
	 * Maybe this should be configurable?
	 * 
	 * (This used to use NOW() in MySQL, but this returns the time including DST, so I've
	 * switched to using DateTime instead).
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
				SET due_at = :timestamp
				WHERE id = :repo_id
		";
		$timestamp = new \DateTime();
		$timestamp->add(new \DateInterval("PT{$minutes}M"));

		return $this->runStatement(
			$this->getDriver(),
			$sql,
			array(
				':repo_id' => $repoId,
				':timestamp' => $timestamp->format('Y-m-d H:i:s'),
			)
		);
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
	 * Counts the number of recent consecutive serious failures in the logs
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

	/**
	 * Gets the currently set searcher
	 * 
	 * @return Searcher
	 */
	public function getSearcher()
	{
		return $this->searcher;
	}

	/**
	 * Sets the search module, so we can do document indexing
	 * 
	 * @param Searcher $searcher
	 */
	public function setSearcher(Searcher $searcher)
	{
		$this->searcher = $searcher;
	}
}
