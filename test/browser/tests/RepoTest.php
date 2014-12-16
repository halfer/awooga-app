<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class RepoTest extends TestCase
{
	/**
	 * Ensure the count is correct for this repo
	 * 
	 * @driver phantomjs
	 */
	public function testRepos()
	{
		$this->
			visit(self::DOMAIN . '/repos')->
			assertHasCss('table tbody tr:first-child td:nth-child(2):contains("26")')
		;
	}
}