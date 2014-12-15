 <?php
 
 /*
  * Simple routing file for browser testing. This is started up by the
  * server.sh script.
  */
 
$root = realpath(__DIR__ . '/../../..');

// Specify we're in the test environment
$_ENV['SLIM_MODE'] = 'test';

// Save our process ID for later termination
file_put_contents($root . '/.server.pid', getmypid());

if (preg_match('#^/assets/#', $_SERVER["REQUEST_URI"]))
{
	// Let static assets fall through to the default server
	return false;
}
else
{
	// Send page requests to Slim
	include $root . "/web/index.php";
}
