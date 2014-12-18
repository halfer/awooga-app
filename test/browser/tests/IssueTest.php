<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class IssueTest extends TestCase
{
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