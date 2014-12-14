<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class HomeTest extends TestCase
{
	const DOMAIN = 'http://awooga.local';

	/**
	 * Ensure the menus and footers look OK
	 * 
	 * @driver phantomjs
	 */
	public function testBasicPageShape()
	{
		$this->
			// A help section
			visit(self::DOMAIN)->
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

			// Report/issues counter (line break means two tests are necessary)
			assertHasCss("nav p:contains('Awooga has 54 issues')")->
			assertHasCss("nav p:contains('in 26 reports')")->

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
		$this->visit(self::DOMAIN);
		$links = $this->find('form small a');

		foreach ($links as $link)
		{
			$link->
				click()->
				assertEquals('/browse', $this->current_path())
			;
			// Go back to home so we can check each link does something
			$this->visit(self::DOMAIN);
		}
	}

	/**
	 * Ensure the More button goes to the about page
	 * 
	 * @driver phantomjs
	 */
	public function testMoreInterestingQuestions()
	{
		$this->
			visit(self::DOMAIN)->
			click_link('more-questions')->
			// This seems needed to help the browser settle before we get the current path
			assertHasCss("h3:contains(\"What's the current focus?\")")
		;
		$this->assertEquals('/about', $this->current_path());
	}
}
