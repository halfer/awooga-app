<?php

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

// Set up the debug bar
$debugbar = new DebugBar\StandardDebugBar();
$jsRenderer = $debugbar->getJavascriptRenderer('/assets/debugbar');

// Set up the framework and template system
$app = new \Slim\Slim();
$app->debugbar = $debugbar;
$engine = new \League\Plates\Engine($root . '/src/templates');
$engine->addData(
	array('debugbarRenderer' => $jsRenderer, )
);

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

// Set up common front controller for browsing
$browse = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Browse($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();	
};

// Browse screen with and without pages
$app->get('/browse', $browse);
$app->get('/browse/:page', $browse);

// A controller for a single report
$app->get('/report/:report', function($reportId) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Report($app, $engine);
	$controller->setReportId($reportId);
	$controller->initAndExecute();
});

// Set up a repos screen
$issues = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Issues($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();
};
$app->get('/issues', $issues);
$app->get('/issues/:page', $issues);

// Set up a repos screen
$repos = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Repos($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();
};
$app->get('/repos', $repos);
$app->get('/repos/:page', $repos);

// Set up log screen
$log = function($page = null) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Logs($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();
};
$app->get('/logs', $log);
$app->get('/logs/:page', $log);

// Thunderbirds are go!
$app->run();
