<?php

namespace Awooga\Testing\Browser;

class BrowseTest extends TestCase
{
	/**
	 * General table browsing
	 * 
	 * @todo Need a page title test
	 * 
	 * @driver phantomjs
	 */
	public function testBrowse()
	{
		$this->
			visit(self::DOMAIN . '/browse')->

			// Check the pagination device is present
			assertHasCss('#paginator')->
				
			// Check the report link goes to /report/x
			assertHasCss('table#reports tbody tr:first-child td:first-child a[href="/report/26"]');
	}

	/**
	 * Check that domains can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testDomainSearch()
	{
		$this->checkSearch('phppot.com', 11);
	}

	/**
	 * Check that keywords can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testKeywordSearch()			
	{
		$this->checkSearch('AJAX', 10);
	}

	/**
	 * Check that a title can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testTitleSearch()
	{
		$this->checkSearch('"Creating a Login System in PHP"', 1);
	}

	/**
	 * Check that a single URL can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testUrlSearch()
	{
		$this->checkSearch('http://www.amitpatil.me/youtube-like-rating-script-jquery-php/', 1);
	}

	/**
	 * Check that issue keywords can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testIssueSearch()
	{
		$this->checkSearch('sql-injection', 23, true);
	}

	/**
	 * Generalised method to check if the search module is working
	 * 
	 * @param string $search
	 * @param integer $expectedResults
	 * @param boolean $hasPaginator
	 */
	protected function checkSearch($search, $expectedResults, $hasPaginator = false)
	{
		$assertHasPaginator = $hasPaginator ? 'assertHasCss' : 'assertHasNoCss';
		$this->
			visit(self::DOMAIN . '/browse?search=' . urlencode($search))->
			assertHasCss("#addressSearch[value*='$search']")->
			assertHasCss("h3:contains('$expectedResults results')")->
			$assertHasPaginator('#paginator')
		;
	}
}