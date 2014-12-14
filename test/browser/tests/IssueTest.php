<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class IssueTest extends TestCase
{
	const DOMAIN = 'http://awooga.local';

	public function testIssues()
	{
		// Check counts for each issue
	}
}