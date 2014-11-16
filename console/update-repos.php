<?php

/* 
 * Script to update repositories in mounted file system
 * 
 * This should be run as the 'awooga' user, which has deliberately limited permissions
 */

$root = dirname(__DIR__);

// Connect to the database
// @todo Pull this from env config
$dsn = 'mysql:dbname=awooga;host=localhost;username=awooga_user;password=password';
$pdo = new PDO($dsn, 'awooga_user', 'password');

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
	processRepo($pdo, $row['id'], $row['url'], $row['mount_path']);
}

function processRepo(PDO $pdo, $repoId, $url, $mountPath)
{
	// Try a new clone
	try
	{
		// @todo Pull to a temporary location
		repoLog($pdo, $repoId, 'fetch');
	}
	catch (Exception $e)
	{
		repoLog($pdo, $repoId, 'fetch', 'Fetch failed', false);
	}

	// Try moving the clone into place

	// Log that info
	// Scan repo
	// Add to database
	// Log that info
}

function doClone($url, $target)
{
	// The reset of HOME is to prevent Git trying to fetch config it doesn't have access to
	$command = "HOME='' git clone {$url} {$target}";
}

function repoLog(PDO $pdo, $repoId, $logType, $message = null, $isSuccess = true)
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
	$statement = $pdo->prepare($sql);
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