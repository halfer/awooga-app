<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class LogTest extends TestCase
{
	/**
	 * Check logs pagination works
	 * 
	 * @driver phantomjs
	 */
	public function testLogs()
	{
		// @todo Improve fixture data first, too much cruft at the mo
		// @todo Need a page title test

	}
}