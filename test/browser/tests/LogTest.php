<?php

namespace Awooga\Testing\Browser;

class LogTest extends TestCase
{
	/**
	 * Check page title
	 * 
	 * @driver simple
	 */
	public function testTitle()
	{
		$page = $this->visit($this->getTestDomain() . '/logs');
		$this->assertEquals('Import logs — Awooga', $page->find('title')->text());
	}

	/**
	 * Check logs pagination works
	 * 
	 * @driver phantomjs
	 */
	public function testLogs()
	{
		// @todo Improve fixture data first, too much cruft at the mo
	}
}