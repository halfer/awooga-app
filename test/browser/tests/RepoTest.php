<?php

namespace Awooga\Testing\Browser;

class RepoTest extends TestCase
{
	/**
	 * Check page title
	 * 
	 * @driver simple
	 */
	public function testTitle()
	{
		$page = $this->visit(self::DOMAIN . '/repos');
		$this->assertEquals('Source repositories — Awooga', $page->find('title')->text());
	}

	/**
	 * Ensure the count is correct for this repo
	 * 
	 * @driver phantomjs
	 */
	public function testRepos()
	{
		$this->
			visit(self::DOMAIN . '/repos')->
			assertHasCss('table tbody tr:first-child td:nth-child(2):contains("24")')
		;
	}
}