 <?php
 
 /*
  * Simple routing file for browser testing. From the project root, just run:
  * 
  * php -S localhost:8090 -t web test/browser/router.php
  */
 
$root = realpath(__DIR__ . '/../..');

// Specify we're in the test environment
$_ENV['SLIM_MODE'] = 'test';

// Let static assets fall through to the default server
if (preg_match('#^/assets/#', $_SERVER["REQUEST_URI"])) {
	return false;
} else {
	include $root . "/web/index.php";
}
