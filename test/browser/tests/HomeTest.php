<?php

namespace Awooga\Testing\Browser;

class HomeTest extends \Openbuildings\PHPUnitSpiderling\Testcase_Spiderling
{
	/**
	 * Ensure the menus and footers look OK
	 * 
	 * @driver phantomjs
	 */
	public function testBasicPageShape()
	{
		$this->
			// A help section
			visit('http://awooga.local/')->
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
			assertHasCss("nav p:contains('Awooga has 53 issues')")->
			assertHasCss("nav p:contains('in 25 reports')")->

			// Footer
			assertHasCss("footer a:contains('GitHub')")->
			assertHasCss("footer a:contains('Twitter')")
		;
	}

	/**
	 * Grab all the example searches and make sure they land on the right page
	 */
	public function testExampleSearches()
	{
		
	}

	/**
	 * Ensure the More button goes to the about page
	 * 
	 * @driver phantomjs
	 */
	public function testMoreInterestingQuestions()
	{
		$this->
			// A help section
			visit('http://awooga.local/')->
			click_link('more-questions')->
			// This seems needed to help the browser settle before we get the current path
			assertHasCss("h3:contains(\"What's the current focus?\")")
		;
		$this->assertEquals('/about', $this->current_path());
	}
}
