<?php

/* 
 * Script to update repositories in mounted file system
 * 
 * This should be run as the 'awooga' user, which has deliberately limited permissions
 */

$root = dirname(__DIR__);
$repoRoot = $root . '/filesystem/mount';

// Connect to the database
// @todo Pull this from env config
$dsn = 'mysql:dbname=awooga;host=localhost;username=awooga_user;password=password';
$pdo = new PDO($dsn, 'awooga_user', 'password');

// Set up importer system
$importer = new GitImporter($pdo, $repoRoot, true);

$sql = '
	SELECT * FROM repository
	WHERE
		is_enabled = true AND
		(due_at IS NULL OR NOW() > due_at)
	ORDER BY
		updated_at
	LIMIT 10
';
$statement = $pdo->prepare($sql);
$statement->execute();

while ($row = $statement->fetch(PDO::FETCH_ASSOC))
{
	$importer->processRepo($row['id'], $row['url'], $row['mount_path']);
}

class GitImporter
{
	const LOG_TYPE_FETCH = 'fetch';
	const LOG_TYPE_MOVE = 'move';
	const LOG_TYPE_SCAN = 'scan';

	protected $pdo;
	protected $repoRoot;
	protected $debug;

	public function __construct(PDO $pdo, $repoRoot, $debug = false)
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
		catch (Exception $e)
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
		catch (Exception $e)
		{
			$this->repoLog($repoId, self::LOG_TYPE_MOVE, "Move from $oldPath to $newPath failed", false);
			return false;
		}

		// Scan repo
		// Add to database
		// Log that info
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
			throw new Exception("Problem when cloning");
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
			throw new Exception("Updating the repo path failed");
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
				throw new Exception("Problem when deleting the old repo");
			}

			$this->writeDebug("Remove old location '{$oldPath}' for repo #{$repoId}");
		}
	}

	public function repoLog($repoId, $logType, $message = null, $isSuccess = true)
	{
		// Check the type is OK
		$allowedTypes = array(self::LOG_TYPE_FETCH, self::LOG_TYPE_MOVE, self::LOG_TYPE_SCAN, );
		if (!in_array($logType, $allowedTypes))
		{
			throw new Exception("The supplied type is not valid");
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
			throw new Exception('Adding a log message seems to have failed');
		}

		$this->writeDebug("Adding log message of type {$logType}");
	}

	protected function writeDebug($message)
	{
		if ($this->debug)
		{
			echo $message . "\n";
		}
	}
}