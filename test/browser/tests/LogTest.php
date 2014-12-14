<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class LogTest extends TestCase
{
	const DOMAIN = 'http://awooga.local';

	public function testLogs()
	{
		// Check logs pagination works
	}
}