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

$repoRoot = $root . '/filesystem/mount/repos';
$searchIndex = $root . '/filesystem/mount/search-index';

// Connect to the database (@todo Move this to env config)
$dsn = 'mysql:dbname=awooga;host=localhost;username=awooga_user;password=password';
$pdo = new PDO($dsn, 'awooga_user', 'password');

// Add a time limit to prevent crons overlapping (@todo Move this to config). Maybe would be
// better though to detect instance of this script and exit if it is running?
set_time_limit(60 * 6);

// Set up importer, search indexer, updater
$importer = new GitImporter($repoRoot, true);
$importer->setDriver($pdo);
$searcher = new Searcher();
$searcher->connect($searchIndex);
$importer->setSearcher($searcher);
$updater = new UpdateAll($importer);
$updater->setDriver($pdo);

// Give it a whirl!
$updater->run(20, false);
