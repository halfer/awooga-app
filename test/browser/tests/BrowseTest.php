<?php

namespace Awooga\Testing\Browser;

class BrowseTest extends TestCase
{
	/**
	 * Check page title
	 * 
	 * @todo Works with simple driver but not phantomjs, reported here:
	 * https://github.com/OpenBuildings/spiderling/issues/7
	 * 
	 * @driver simple
	 */
	public function testTitle()
	{
		$page = $this->visit(self::DOMAIN . '/browse');
		$this->assertEquals('Browse reports — Awooga', $page->find('title')->text());
	}

	/**
	 * General table browsing
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
	 * Test the owner strings of the top two reports
	 * 
	 * @driver phantomjs
	 */
	public function testSourceStrings()
	{
		$page = $this->visit(self::DOMAIN . '/browse');

		// Check the report link goes to /report/x
		$firstSource = $page->find('table#reports tbody tr:first-child td:last-child')->text();
		$this->assertEquals('User: github.com/halfer', $firstSource);

		$secondSource = $page->find('table#reports tbody tr:nth-child(2) td:last-child')->text();
		$this->assertEquals('Repo: 1', $secondSource);
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
	 * This caused a false fail on 2015-01-14, and re-running in Travis fixed the issue. Maybe we
	 * need to add in a conditional screenshot just before the checkTableRowCount call? Very odd,
	 * keep an eye on this.
	 * 
	 * @todo Another one for PHP 5.5 on 2015-01-15. Add screenshot to see what's going on?
	 * 
	 * @driver phantomjs
	 */
	public function testIssueSearch()
	{
		$this->checkSearch('sql-injection', 23, true);

		// Make sure paginator works on searches
		$originalUrl = $this->current_url();
		$this->
			find('#paginator')->
			click_link('2');
		$this->assertTrue(
			$this->waitUntilRedirected($originalUrl),
			"Ensure the search moves to the second page"
		);
		$this->checkTableRowCount(3);
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

		// Count table entries too, in case the query has borked, or there are too many entries
		$this->checkTableRowCount($expectedResults);
	}

	protected function checkTableRowCount($expectedResults)
	{
		$rows = $this->all('#reports tbody tr');
		$this->assertEquals(
			$expectedResults > 20 ? 20 : $expectedResults,
			count($rows),
			"Check the correct number of results have been rendered on the (first) results page"
		);		
	}
}