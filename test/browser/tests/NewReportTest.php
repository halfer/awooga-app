<?php

namespace Awooga\Testing\Browser;

class NewReportTest extends TestCase
{
	/**
	 * Checks the menu link only appears when logged in
	 * 
	 * @driver phantomjs
	 */
	public function testNewReportInMenu()
	{
		$this->assertNotContains('New report', $this->getNavBarText());

		// Check again when logged in
		$this->loginTestUser();
		$this->assertContains('New report', $this->getNavBarText());
	}

	/**
	 * Gets the contents of the current nav bar
	 * 
	 * @return string
	 */
	protected function getNavBarText()
	{
		return $this->visit(self::DOMAIN)->find('#navbar')->text();
	}

	/**
	 * Check that the new report page does not show for anonymous users
	 * 
	 * @driver phantomjs
	 */
	public function testNewReportRequiresLogin()
	{
		$this->visit($this->pageUrl());
		$this->assertEquals('/auth?require-auth=1', $this->current_path());
	}

	/**
	 * Check that the new report form seems to show OK
	 * 
	 * @driver phantomjs
	 */
	public function testShowNewReport()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		$tableText = $this->visit($this->pageUrl())->find('form#edit-report')->text();
		$labels = array('URL(s)', 'Title', 'Description', 'Issue(s)', 'Author notified date', );
		foreach ($labels as $label)
		{
			$this->assertContains($label . ':', $tableText);
		}
	}

	/**
	 * Check that some simple validation works for this page
	 * 
	 * @driver phantomjs
	 */
	public function testBasicValidation()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		// No URL
		$this->
			visit($this->pageUrl())->
			click_button('Save');
		$this->checkError("The 'url' field is required");

		// Bad URL
		$this->
			setPageData('nonsense')->
			click_button('Save');
		$this->checkError('The URL "nonsense" does not have a recognised protocol');

		// Insert a URL, add a new URL, insert an identical URL
		$this->
			setPageData('http://urlone.com/');
		$this->
			addAnotherUrl()->
			find('#edit-report div.url-group:nth-child(2) input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			click_button('Save');
		$this->checkError("URL arrays may not contain duplicates");

		// The URL duplicates a URL in a report belonging to the same user
		$this->
			setPageData('http://www.smarttutorials.net/responsive-quiz-application-using-php-mysql-jquery-ajax-and-twitter-bootstrap/')->
			click_button('Save');
		$this->checkError('One of these URLs is already contained within another of your reports');
				
		// Missing title
		$this->
			setPageData('http://urlone.com/')->
			click_button('Save');
		$this->checkError("The 'title' field is required");

		// Missing description
		$this->
			setPageData('http://urlone.com/', 'Demo title')->
			click_button('Save');
		$this->checkError("The 'description' field is required");
	}

	/**
	 * Check that some simple length validation works for this page
	 * 
	 * @driver phantomjs
	 */
	public function testBasicLengthValidation()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		// Excessively long URL
		$this->
			setPageData('http://' . str_repeat('nonsense', 50))->
			click_button('Save');
		$this->checkError("The 'url' field cannot be longer than 256 characters");

		// Excessively long title
		$this->setPageData('http://urlone.com/', str_repeat('Demo title', 50))->
			click_button('Save');
		$this->checkError("The 'title' field cannot be longer than 256 characters");

		// Excessively long description
		$this->setPageData(
			'http://urlone.com/',
			'Demo title',
			str_repeat('Demo description', 100)
		)->
			click_button('Save');
		$this->checkError("The 'description' field cannot be longer than 1024 characters");

		// Excessively long issue description
		$this->setPageData(
			'http://urlone.com/',
			'Demo title',
			'Demo description'
		)->
			find('#edit-report textarea[name="issue-description[]"]')->
				set(str_repeat('Demo description', 100))->
			end()->
			click_button('Save');
		$this->checkError("The 'issue-description' field cannot be longer than 1024 characters");
	}

	/**
	 * Checks that two issues of the same code result in a validation error
	 * 
	 * @driver phantomjs
	 */
	public function testDuplicateIssueCode()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		$this->setPageData(
			'http://urlone.com/',
			'Demo title',
			'Demo description'
		);
		$this->addAnotherIssue();

		// Grab the selects and set them to the same value
		$selects = $this->all('#edit-report div.issue-group select');
		foreach ($selects as $select)
		{
			$select->select_option('sql-injection')->end();
		}

		$this->click_button('Save');
		$this->checkError("Issue codes may not be duplicated in a report");
	}

	/**
	 * Validation tests that require page hacking
	 * 
	 * @driver phantomjs
	 */
	public function testAdvancedValidation()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		// @todo
		// No URLs
		// No issues
		// Invalid issue code
		// Excessively long issue code
	}

	/**
	 * Check that we can actually save an item!
	 * 
	 * @driver phantomjs
	 */
	public function testSuccessfulSave()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		$this->setPageData(
			'http://urlone.com/',
			'Demo title',
			'Demo description'
		)->
			find('#edit-report textarea[name="issue-description[]"]')->
				set('This is a demo issue description')->
			end()->
			find('#edit-report select[name="issue-type-code[]"]')->
				select_option('sql-injection')->
			end()->
			click_button('Save');

		// Wait for the redirect
		$redirected = $this->waitUntilRedirected($this->pageUrl());
		$this->assertTrue($redirected, "Ensure page redirects to different URL");

		// Check there is no error message
		$this->not_present('.alert-warning');
	}

	/**
	 * Check that the JavaScript device to remove a URL works
	 * 
	 * @driver phantomjs
	 */
	public function testRemoveUrlWidget()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		$this->visit($this->pageUrl());
		$this->addAnotherUrl();
		$this->addAnotherUrl();
		$this->addAnotherUrl();

		// Add some data
		for($i = 1; $i <= 4; $i++)
		{
			$this->setSpecificUrl($i, 'http://example.com/' . $i);
		}

		// Remove the first one and the last one, check the middle two are still OK
		$this->
			find('#edit-report div.url-group:nth-child(1)')->
				click_button('-')->
			end()->
			// The 4th one becomes the 3rd after the above call!
			find('#edit-report div.url-group:nth-child(3)')->
				click_button('-')->
			end();
		$this->not_present('#edit-report div.url-group input:contains("http://example.com/1")');
		$this->not_present('#edit-report div.url-group input:contains("http://example.com/4")');
		$this->assertEquals(
			'http://example.com/2',
			$this->find('#edit-report div.url-group:nth-child(1) input')->value()
		);
		$this->assertEquals(
			'http://example.com/3',
			$this->find('#edit-report div.url-group:nth-child(2) input')->value()
		);
	}

	/**
	 * Check that the JavaScript device to remove an issue group works
	 * 
	 * @driver phantomjs
	 */
	public function testRemoveIssueWidget()
	{
		// @todo Check that with two issues, the last one can be removed
		// @todo Check that with three issues, the first one can be removed
	}

	/**
	 * A utility method to check an error is present
	 * 
	 * @param string $errorMessage
	 */
	protected function checkError($errorMessage)
	{
		$this->assertContains(
			$errorMessage,
			$this->find('.alert-warning')->text()
		);		
	}

	/**
	 * Shortcut method to fill in some of the form's fields
	 * 
	 * @param string $url
	 * @param string $title
	 * @param string $description
	 */
	protected function setPageData($url = '', $title = '', $description = '')
	{
		$this->visit($this->pageUrl());

		return $this->
			setSpecificUrl(1, $url)->
			find('#edit-report input[name="title"]')->
				set($title)->
			end()->
			find('#edit-report textarea[name="description"]')->
				set($description)->
			end();
	}

	/**
	 * Clicks the + URL button
	 * 
	 * @return Node
	 */
	protected function addAnotherUrl()
	{
		return $this->
			find('#edit-report .url-group')->
				click_button('+')->
			end();
	}

	/**
	 * Clicks the + issue button
	 * 
	 * @return Node
	 */
	protected function addAnotherIssue()
	{
		return $this->
			find('#edit-report .issue-group')->
				click_button('+')->
			end();		
	}

	/**
	 * Sets the first/second/etc URL input
	 * 
	 * @param integer $ord
	 * @param string $url
	 * @return string
	 */
	protected function setSpecificUrl($ord, $url)
	{
		return $this->
			find("#edit-report div.url-group:nth-child($ord) input[name='urls[]']")->
				set($url)->
			end();
	}

	protected function waitUntilRedirected($originalUrl)
	{
		$retry = 0;
		do {
			$hasRedirected = $originalUrl != $this->current_url();
			if (!$hasRedirected)
			{
				sleep(0.5);
			}
		} while (!$hasRedirected && $retry++ < 20);

		return $hasRedirected;
	}

	protected function pageUrl()
	{
		return self::DOMAIN . '/report/new';
	}
}