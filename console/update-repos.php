<?php

/* 
 * Script to update repositories in mounted file system
 * 
 * This should be run as the 'awooga' user, which has deliberately limited permissions
 */

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

use Awooga\Core\GitImporter;
use Awooga\Core\Searcher;
use Awooga\Core\UpdateAll;

# Get an environment name
$envName = getenv('SLIM_MODE');
if (!$envName)
{
    echo "Error: needs an env var of `SLIM_MODE`\n";
    exit(1);
}

# Get the config file
$configPath = $root . '/config/env-config.php';
$allConfig = include($configPath);
$config = $allConfig[$envName]['database'];

// Connect to the database using config values
$dsn = "mysql:dbname={$config['database']};host={$config['host']};username={$config['username']};password={$config['password']}";
$pdo = new PDO($dsn, 'awooga_user', 'password');

$repoRoot = $root . '/filesystem/mount/repos';
$searchIndex = $root . '/filesystem/mount/search-index';

// Set up importer, search indexer, updater
$importer = new GitImporter($repoRoot, true);
$importer->setDriver($pdo);
$searcher = new Searcher();
$searcher->connect($searchIndex);
$importer->setSearcher($searcher);
$updater = new UpdateAll($importer);
$updater->setDriver($pdo);

// No time limit
set_time_limit(0);

// Here's a rudimentary loop to perform an import every 10 mins
while (true)
{
    $updater->run(20, false);
    sleep(10 * 60);
}
