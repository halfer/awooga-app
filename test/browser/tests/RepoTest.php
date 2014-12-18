<?php

namespace Awooga\Testing\Browser;

class RepoTest extends TestCase
{
	/**
	 * Ensure the count is correct for this repo
	 * 
	 * @todo Need a page title test
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