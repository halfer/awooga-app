<?php

/* 
 * Script to update repositories in mounted file system
 * 
 * This should be run as the 'awooga' user, which has deliberately limited permissions
 */

$root = dirname(__DIR__);
$repoRoot = $root . '/filesystem/mount';

// Load library files
require_once $root . '/src/classes/GitImporter.php';
require_once $root . '/src/classes/Report.php';

// Connect to the database
// @todo Pull this from env config
$dsn = 'mysql:dbname=awooga;host=localhost;username=awooga_user;password=password';
$pdo = new PDO($dsn, 'awooga_user', 'password');

// Set up importer system
$importer = new Awooga\GitImporter($pdo, $repoRoot, true);

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
