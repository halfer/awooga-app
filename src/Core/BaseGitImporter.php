<?php

namespace Awooga\Core;

use \Awooga\Exceptions\SeriousException;

class BaseGitImporter
{
	const LOG_TYPE_FETCH = 'fetch';
	const LOG_TYPE_MOVE = 'move';
	const LOG_TYPE_SCAN = 'scan';
	const LOG_TYPE_RESCHED = 'resched';

	const LOG_LEVEL_SUCCESS = 'success';
	const LOG_LEVEL_ERROR_TRIVIAL = 'trivial';
	const LOG_LEVEL_ERROR_SERIOUS = 'serious';

	protected $repoRoot;
	protected $debug = false;

	use Database;

	/**
	 * Deletes a folder from the filing system
	 * 
	 * @param string $oldPath
	 * @return boolean True on success
	 * @throws SeriousException
	 */
	protected function deleteOldRepo($oldPath)
	{
		// Halt if there's no root, to avoid a dangerous command :)
		if (!$this->repoRoot)
		{
			throw new SeriousException(
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