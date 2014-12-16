<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class BrowseTest extends TestCase
{
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
	 * Check that domains can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testDomainSearch()
	{
		$this->
			visit(self::DOMAIN . '/browse?search=phppot.com')->
			assertHasCss('#addressSearch[value="phppot.com"]')->
			assertHasCss('h3:contains("11 results")')->
			assertHasNoCss('#paginator')
		;
	}

	/**
	 * Check that keywords can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testKeywordSearch()			
	{
		$this->
			visit(self::DOMAIN . '/browse?search=AJAX')->
			assertHasCss('#addressSearch[value="AJAX"]')->
			assertHasCss('h3:contains("10 results")')->
			assertHasNoCss('#paginator')
		;
	}

	/**
	 * Check that a title can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testTitleSearch()
	{
		$search = '"Creating a Login System in PHP"';
		$this->
			visit(self::DOMAIN . '/browse?search=' . urlencode($search))->
			// @todo &quot; didn't seem to work here - are literal quotes better?
			assertHasCss("#addressSearch[value*='$search']")->
			assertHasCss('h3:contains("1 results")')->
			assertHasNoCss('#paginator')
		;
	}

	/**
	 * Check that a single URL can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testUrlSearch()
	{
		$search = 'http://www.amitpatil.me/youtube-like-rating-script-jquery-php/';
		$this->
			visit(self::DOMAIN . '/browse?search=' . urlencode($search))->
			assertHasCss("#addressSearch[value*='$search']")->
			assertHasCss('h3:contains("1 results")')->
			assertHasNoCss('#paginator')
		;
	}

	/**
	 * Check that issue keywords can be searched for
	 * 
	 * @driver phantomjs
	 */
	public function testIssueSearch()
	{
		$this->
			visit(self::DOMAIN . '/browse?search=sql-injection')->
			assertHasCss("#addressSearch[value='sql-injection']")->
			assertHasCss('h3:contains("23 results")')->
			assertHasCss('#paginator')
		;		
	}
}