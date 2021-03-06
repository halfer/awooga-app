<?php

namespace Awooga\Testing\Browser;

class HomeTest extends TestCase
{
	/**
	 * Check page title
	 * 
	 * @driver simple
	 */
	public function testTitle()
	{
		$page = $this->visit($this->getTestDomain());
		$this->assertEquals('Awooga', $page->find('title')->text());
	}

	/**
	 * Ensure the menus and footers look OK
	 * 
	 * @driver phantomjs
	 */
	public function testBasicPageShape()
	{
		$this->
			// A help section
			visit($this->getTestDomain())->
			// Check a help section is present
			assertHasCss("h3:contains('What does a listing mean?')")->
			// Check our assertions are working by testing something that isn't there
			assertHasNoCss("h3:contains('Random string')")->

			// Menu
			assertHasCss("nav a:contains('Awooga')")->
			assertHasCss("nav a:contains('Browse')")->
			assertHasCss("nav a:contains('Issues')")->
			assertHasCss("nav a:contains('Repositories')")->
			assertHasCss("nav a:contains('Logs')")->
			assertHasCss("nav a:contains('About')")->

			// Report/issues badges
			assertHasCss("nav ul li.nav-issues span.badge:contains('54')")->
			assertHasCss("nav ul li.nav-reports span.badge:contains('26')")->

			// Footer
			assertHasCss("footer a:contains('GitHub')")->
			assertHasCss("footer a:contains('Twitter')")
		;
	}

	/**
	 * Grab all the example searches and make sure they land on the right page
	 * 
	 * @driver phantomjs
	 */
	public function testExampleSearches()
	{
		$links = $this->visit($this->getTestDomain())->all('form small a[href^="/browse"]');
		$this->assertEquals(5, count($links), "Check we have the right number of example searches");
	}

	/**
	 * Ensure the More button goes to the about page
	 * 
	 * @driver phantomjs
	 */
	public function testMoreInterestingQuestions()
	{
		$this->
			visit($this->getTestDomain())->
			click_link('more-questions')->
			// This seems needed to help the browser settle before we get the current path
			assertHasCss("h3:contains(\"What's the current focus?\")")
		;
		$this->assertEquals('/about', $this->current_path());
	}
}
