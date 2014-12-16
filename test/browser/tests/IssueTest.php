<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class IssueTest extends TestCase
{
	/**
	 * Check the issues table works
	 * 
	 * @driver phantomjs
	 */
	public function testIssues()
	{
		// Check number of issues
		$this->visit(self::DOMAIN . '/issues');
		$rows = $this->all('table tbody tr');
		$this->assertEquals(10, count($rows));

		// Check the counts for the first issue
		$this->assertHasCss('table tbody tr:first-child td:last-child:contains("23")');
	}
}