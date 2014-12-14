<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class BrowseTest extends TestCase
{
	const DOMAIN = 'http://awooga.local';

	/**
	 * General table browsing
	 * 
	 * @driver phantomjs
	 */
	public function testBrowse()
	{
		// Check the pagination device is present

		// Check the report link goes to /report/x
	}

	public function testDomainSearch()
	{
		
	}

	public function testTitleSearch()
	{
		
	}

	public function testUrlSearch()
	{
		
	}

	public function testIssueSearch()
	{
		
	}

	public function testNonPaginatedSearch()
	{
		// Check we get the right number of results

		// Check that a small search does not have a pagination device
	}

	public function testPaginatedSearch()
	{
		// Check we get the right number of results

		// Check that the search term appears in the pagination device
	}
}