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
		$this->visit(self::DOMAIN . '/report/new');
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

		$tableText = $this->visit(self::DOMAIN . '/report/new')->find('form#edit-report')->text();
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
		$pageUrl = self::DOMAIN . '/report/new';

		// No URL
		$this->
			visit($pageUrl)->
			find_button('Save')->
				click()->
			end();
		$this->checkError("The 'url' field is required");

		// Bad URL
		$this->
			visit($pageUrl)->
			find('#edit-report input[name="urls[]"]')->
				set('nonsense')->
			end()->
			find_button('Save')->
				click()->
			end();
		$this->checkError('The URL "nonsense" does not have a recognised protocol');

		// Insert a URL, add a new URL, insert an identical URL
		$this->
			visit($pageUrl)->
			find('#edit-report input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find('#edit-report .url-group')->
				click_button('+')->
			end()->
			find('#edit-report div.url-group:nth-child(2) input[name="urls[]"]')->
				set('http://urlone.com/')->
			end()->
			find_button('Save')->
				click();
		$this->checkError("URL arrays may not contain duplicates");

		// One URL duplicating an existing URL
		// Missing title
		// Missing description
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
		// Excessively long title
		// Excessively long description
		// Excessively long issue code
		// Excessively long issue code		
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
	}
}