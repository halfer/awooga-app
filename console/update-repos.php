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
$importer = new GitImporter($pdo, $repoRoot);

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
	protected $pdo;
	protected $repoRoot;

	public function __construct(PDO $pdo, $repoRoot)
	{
		$this->pdo = $pdo;
		$this->repoRoot = $repoRoot;
	}

	public function processRepo($repoId, $url, $mountPath)
	{
		// Try a new clone
		try
		{
			$repoPath = $this->doClone($url, $mountPath);
			$this->repoLog($repoId, 'fetch');
		}
		catch (Exception $e)
		{
			$this->repoLog($repoId, 'fetch', 'Fetch failed', false);
			return false;
		}

		// Try moving the clone into place
		$this->moveRepoLocation($repoId, $repoPath);

		// Log that info
		// Scan repo
		// Add to database
		// Log that info
	}

	public function doClone($url, $target)
	{
		// Set up return vars
		$output = $return = null;

		// If there's no target, let's make one
		if (!$target)
		{
			$target = sha1($url . $target . time());
		}

		// Turn relative target into fully qualified path
		$fqTarget = $this->repoRoot . '/' . $target;

		// Emptying HOME is to prevent Git trying to fetch config it doesn't have access to
		$command = "HOME='' git clone {$url} {$fqTarget}";
		exec($command, $output, $return);
		echo "Run: $command\n";

		if ($return)
		{
			throw new Exception("Problem when cloning");
		}

		return $target;
	}

	public function moveRepoLocation($repoId, $repoPath)
	{
		// 
	}

	public function repoLog($repoId, $logType, $message = null, $isSuccess = true)
	{
		// Check the type is OK
		if (!in_array($logType, array('fetch', 'move', 'scan', )))
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
	}
}