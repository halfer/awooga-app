<?php

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

// The Slim docs say we have to start a session before creating the Slim app
session_start();

// Set up the framework and template system (mode comes from SLIM_MODE in vhost)
$app = new \Slim\Slim();
$engine = new \League\Plates\Engine($root . '/src/templates');

// Set up some routing conditions
$pageCondition = array('page' => '\d+');
$reportCondition = array('report' => '\d+');

// This provides an introduction and a search screen
$app->get('/', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Home($app, $engine);
	$controller->initAndExecute();
});

// Here is an about screen
$app->get('/about', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\About($app, $engine);
	$controller->initAndExecute();
});

// Auth screen
$app->get('/auth', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Auth($app, $engine);
	$controller->initAndExecute();
});

// Log out link
$app->get('/logout', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Logout($app, $engine);
	$controller->initAndExecute();
});

// Set up common front controller for browsing
$browse = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Browse($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();	
};

// Browse screen with and without pages
$app->get('/browse', $browse);
$app->get('/browse/:page', $browse)->conditions($pageCondition);

// A controller to create a new report
$app->map('/report/new', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\NewReport($app, $engine);
	$controller->initAndExecute();
})->via('GET', 'POST');

// A controller for a single report
$app->get('/report/:report', function($reportId) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Report($app, $engine);
	$controller->setReportId($reportId);
	$controller->initAndExecute();
})->conditions($reportCondition);

// A controller for editing a single report
$app->map('/report/:report/edit', function($reportId) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\EditReport($app, $engine);
	$controller->setReportId($reportId);
	$controller->initAndExecute();
})->via('GET', 'POST')->conditions($reportCondition);

// Set up a repos screen
$issues = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Issues($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();
};
$app->get('/issues', $issues);
$app->get('/issues/:page', $issues)->conditions($pageCondition);

// Set up a repos screen
$repos = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Repos($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();
};
$app->get('/repos', $repos);
$app->get('/repos/:page', $repos)->conditions($pageCondition);

// Set up log screen
$log = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Logs($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();
};
$app->get('/logs', $log);
$app->get('/logs/:page', $log)->conditions($pageCondition);

// Thunderbirds are go!
$app->run();
