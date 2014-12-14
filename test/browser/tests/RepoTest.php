<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class RepoTest extends TestCase
{
	const DOMAIN = 'http://awooga.local';

	public function testRepos()
	{
		// Check counts for each repo
	}
}