<?php

/* 
 * Script to update repositories in mounted file system
 * 
 * This should be run as the 'awooga' user, which has deliberately limited permissions
 */

$root = dirname(__DIR__);
$repoRoot = $root . '/filesystem/mount';

// Load library files
require_once $root . '/src/classes/Database.php';
require_once $root . '/src/classes/GitImporter.php';
require_once $root . '/src/classes/Report.php';
require_once $root . '/src/classes/UpdateAll.php';
require_once $root . '/src/classes/Exceptions/SeriousException.php';
require_once $root . '/src/classes/Exceptions/TrivialException.php';

// Connect to the database
// @todo Pull this from env config
$dsn = 'mysql:dbname=awooga;host=localhost;username=awooga_user;password=password';
$pdo = new PDO($dsn, 'awooga_user', 'password');

// Set up updater
$importer = new Awooga\GitImporter($repoRoot, true);
$importer->setDriver($pdo);
$updater = new Awooga\UpdateAll($importer);
$updater->setDriver($pdo);

// Give it a whirl!
$updater->run(20, false);
