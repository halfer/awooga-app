<?php

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

// Set up the framework and template system
$app = new \Slim\Slim();
$engine = new \League\Plates\Engine($root . '/src/templates');

// This provides an introduction and a search screen
$app->get('/', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Home($app, $engine);
	$controller->initAndExecute();
});

// Set up common front controller for browsing
$browse = function($page = 1) use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Browse($app, $engine);
	$controller->setPage($page);
	$controller->initAndExecute();	
};

// Browse screen with and without pages
$app->get('/browse', $browse);
$app->get('/browse/:page', $browse);

// Set up log screen
$app->get('/logs', function() use ($app, $engine)
{
	$controller = new \Awooga\Controllers\Logs($app, $engine);
	$controller->initAndExecute();
});

// Thunderbirds are go!
$app->run();
