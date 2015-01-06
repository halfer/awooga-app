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
		// @todo Check that we are redirected to login
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
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('nonsense')->
			end()->
			click_button('Save');
		$this->checkError('The URL "nonsense" does not have a recognised protocol');

		// Insert a URL, add a new URL, insert an identical URL
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find('#edit-report .url-group')->
				click_button('+')->
			end()->
			find('#edit-report div.url-group:nth-child(2) input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			click_button('Save');
		$this->checkError("URL arrays may not contain duplicates");

		// The URL duplicates a URL in a report belonging to the same user
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://www.smarttutorials.net/responsive-quiz-application-using-php-mysql-jquery-ajax-and-twitter-bootstrap/')->
			end()->
			click_button('Save');
		$this->checkError('One of these URLs is already contained within another of your reports');
				
		// Missing title
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			click_button('Save');
		$this->checkError("The 'title' field is required");

		// Missing description
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find('#edit-report input[name="title"]')->
				set('Demo title')->
			end()->
			click_button('Save');
		$this->checkError("The 'description' field is required");
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
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://' . str_repeat('nonsense', 50))->
			end()->
			click_button('Save');
		$this->checkError("The 'url' field cannot be longer than 256 characters");

		// Excessively long title
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find('#edit-report input[name="title"]')->
				set(str_repeat('Demo title', 50))->
			end()->
			click_button('Save');
		$this->checkError("The 'title' field cannot be longer than 256 characters");

		// Excessively long description
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find('#edit-report input[name="title"]')->
				set('Demo title')->
			end()->
			find('#edit-report textarea[name="description"]')->
				set(str_repeat('Demo description', 100))->
			end()->
			click_button('Save');
		$this->checkError("The 'description' field cannot be longer than 1024 characters");

		// Excessively long issue description
		$this->
			visit($this->pageUrl())->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find('#edit-report input[name="title"]')->
				set('Demo title')->
			end()->
			find('#edit-report textarea[name="description"]')->
				set('Demo description')->
			end()->
			find('#edit-report textarea[name="issue-description[]"]')->
				set(str_repeat('Demo description', 100))->
			end()->
			click_button('Save');			
		$this->checkError("The 'issue-description' field cannot be longer than 1024 characters");
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

		// No URLs
		// No issues
		// Invalid issue code
		// Excessively long issue code
	}

	protected function pageUrl()
	{
		return self::DOMAIN . '/report/new';
	}
}