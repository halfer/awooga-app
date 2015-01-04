<?php

namespace Awooga\Testing\Browser;

class IssueTest extends TestCase
{
	/**
	 * Check page title
	 * 
	 * @driver simple
	 */
	public function testTitle()
	{
		$page = $this->visit(self::DOMAIN . '/issues');
		$this->assertEquals('Issue types — Awooga', $page->find('title')->text());
	}

	/**
	 * Check the issues table works
	 * 
	 * @todo Need a page title test
	 * 
	 * @driver phantomjs
	 */
	public function testIssues()
	{
		// Check number of issues
		$rows = $this->visit(self::DOMAIN . '/issues')->all('table tbody tr');
		$this->assertEquals(10, count($rows));

		// Check the counts for the first issue
		$this->assertHasCss('table tbody tr:first-child td:last-child:contains("23")');
	}
}