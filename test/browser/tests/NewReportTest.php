<?php

namespace Awooga\Testing\Browser;

class NewReportTest extends TestCase
{
	/**
	 * Handy to check critical web assets are available, which would cause JS not to work
	 */
	public function testWebAssetsAvailable()
	{
		$assets = array(
			array(self::DOMAIN . '/assets/main.css', 700, 1000),
			array(self::DOMAIN . '/assets/jquery.min.js', 80000, 90000)
		);
		foreach ($assets as $asset)
		{
			$data = file_get_contents($asset[0]);
			$size = strlen($data);
			$this->assertTrue(
				$size >= $asset[1] && $size <= $asset[2],
				"Check to see filesize of $size bytes is within expected bounds"
			);
		}
	}

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
			find('#edit-report .url-group:nth-child(2) input[name="urls[]"]')->
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

		// Set an invalid issue resolved date
		$this->
			setPageData('http://urlone.com', 'Demo title', 'Demo description')->
			find('#edit-report input[name="issue-resolved-date[]"]')->
				set('invalid')->
			end()->
			click_button('Save');
		$this->checkError("A resolution date must be in the form yyyy-mm-dd");

		// Set an invalid author notified date
		$this->
			setPageData('http://urlone.com', 'Demo title', 'Demo description')->
			find('#edit-report input[name="author-notified-at"]')->
				set('invalid')->
			end()->
			click_button('Save');
		$this->checkError("An author notified date must be in the form yyyy-mm-dd");
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
		$this->checkError("Issue codes (other than 'uncategorised') may only appear once in a report");
	}

	/**
	 * Validation tests that require page hacking
	 * 
	 * @todo Write this test
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

	/**
	 * Ensure that when validation fails, user input remains in place
	 * 
	 * @todo Write this test
	 */
	public function testValidationFailurePreservesFormContents()
	{
		
	}

	/**
	 * Check that we can actually save an item!
	 * 
	 * @todo Maybe centralise these strings as constants, so they are not duplicated here?
	 * 
	 * @driver phantomjs
	 */
	public function testSave()
	{
		$this->doSimpleSave($issue = 'sql-injection');

		// We are in view mode, and need to check some elements
		$this->
			find('#report')->
				find('tr:first-child td:last-child:contains("http://urlone.com/")')->end()->
				find('tr:nth-child(2)')->
					find("td:nth-child(2):contains('$issue')")->end()->
					find("td:nth-child(4):contains('This is a demo issue description')")->end()->
				end()->
				find('tr:nth-child(3) td:last-child:contains("Demo description")')->end()->
				find('tr:nth-child(4) td:last-child:contains("testuser")')->end()->
				// No author notified
				find('tr:nth-child(5) td:last-child:contains("No")')->end()->
			end();
	}

	/**
	 * Check that the author notification date works separately
	 * 
	 * @todo Write this test
	 */
	public function testSaveWithAuthorNotificationDate()
	{
		
	}

	/**
	 * Check that save works with one uncategorised issue
	 * 
	 * Uncategorised issues are treated slightly different to ordinary ones, so I am testing
	 * them separately.
	 * 
	 * @driver phantomjs
	 */
	public function testSaveWithOneUncategorisedIssue()
	{
		$this->doSimpleSave('uncategorised');
	}

	protected function doSimpleSave($issueCode)
	{
		$this->
			doBasicDataEntry()->
			select('issue-type-code[]', array('text' => $issueCode))->
			click_button('Save');
		$this->checkSaved();
		$this->switchFromEditToView();

		// Check the category
		$this->find("td.issues:contains({$issueCode})");
	}

	/**
	 * Check that save works with an uncategorised item
	 * 
	 * @driver phantomjs
	 */
	public function testSaveWithMultipleUncategorisedIssues()
	{
		$this->doBasicDataEntry();
		$this->addAnotherIssue();

		// Odd, div.issue-group:nth-child(2) doesn't seem to work, so using all() instead
		$issueGroups = $this->all('#edit-report div.issue-group');
		$issueGroups[0]->
			select('issue-type-code[]', array('text' => 'uncategorised'));
		$issueGroups[1]->
			select('issue-type-code[]', array('text' => 'uncategorised'));
		$this->click_button('Save');

		$this->checkSaved();
	}

	protected function doBasicDataEntry()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		return $this->setPageData(
			'http://urlone.com/',
			'Demo title',
			'Demo description'
		)->
			find('#edit-report textarea[name="issue-description[]"]')->
				set('This is a demo issue description')->
			end();
	}

	protected function switchFromEditToView()
	{
		$editUrl = $this->current_url();
		$this->
			click_link('View report');
		$this->waitUntilRedirected($editUrl);
	}

	protected function checkSaved()
	{
		// Wait for the redirect
		$redirected = $this->waitUntilRedirected($this->pageUrl());
		$this->assertTrue($redirected, "Ensure page redirects to different URL");

		// Check there is a success and no error message
		$this->find('.alert-success');
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

		// Set up the additional URL blocks
		$this->visit($this->pageUrl());
		$this->addAnotherUrl();
		$this->addAnotherUrl();
		$this->addAnotherUrl();

		// Type in some data
		$urls = array();
		for($i = 1; $i <= 4; $i++)
		{
			$urls[] = 'http://example.com/' . $i;
			$this->setSpecificUrl($i, $urls[$i - 1]);
		}

		$this->checkRemoveButtonSection('url', $urls);
	}

	/**
	 * Check that the JavaScript device to remove an issue group works
	 * 
	 * @driver phantomjs
	 */
	public function testRemoveIssueWidget()
	{
		// We need to be signed in for this
		$this->loginTestUser();

		// Set up the additional issue blocks
		$this->visit($this->pageUrl());
		$this->addAnotherIssue();
		$this->addAnotherIssue();
		$this->addAnotherIssue();

		// Type in some data
		$dates = array();
		for($i = 0; $i <= 3; $i++)
		{
			$dates[] = '2015-01-0' . ($i + 1);
			$this->setSpecificIssueResolvedDate($i, $dates[$i]);
		}

		$this->checkRemoveButtonSection('issue', $dates);
	}

	/**
	 * General test harness to click the remove button and check the result
	 * 
	 * @param string $type
	 * @param array $values
	 */
	protected function checkRemoveButtonSection($type, array $values)
	{
		$prefix = "#edit-report div.{$type}-group";
		$input = '#input-' . $type;

		// Remove the first one and the last one, check the middle two are still OK
		$blocks = $this->all($prefix);
		$blocks[0]->
			// Check the ID is present in the first row
			find($input)->
			end()->
			click_button('-');

		// Check we still have an id in the first thing to link the label to
		$this->find($prefix . ' ' . $input);

		// Delete the last item, which remains at 3 even though 0 has been deleted
		$blocks[3]->click_button('-');

		// Make sure deleted items have gone
		$this->waitForSelectorCount($prefix, 2);
		$this->not_present($prefix . " input:contains('{$values[0]}')");
		$this->not_present($prefix . " input:contains('{$values[3]}')");

		// Check remaining items are still there
		$blocksAgain = $this->all($prefix);
		$this->assertEquals(
			$values[1],
			$blocksAgain[0]->find('input')->value()
		);
		$this->assertEquals(
			$values[2],
			$blocksAgain[1]->find('input')->value()
		);
	}

	/**
	 * A utility method to check an error is present
	 * 
	 * @param string $errorMessage
	 */
	protected function checkError($errorMessage)
	{
		$this->waitForSelectorCount($selector = '.alert-warning', 1);
		$this->assertContains(
			$errorMessage,
			$this->find($selector)->text()
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
		$this->setSpecificUrl(1, $url);

		return $this->
			find('#edit-report input[name="title"]')->
				set($title)->
			end()->
			find('#edit-report textarea[name="description"]')->
				set($description)->
			end();
	}

	/**
	 * Clicks the + URL button and waits for the screen to update
	 * 
	 * @return \Openbuildings\Spiderling\Node
	 */
	protected function addAnotherUrl()
	{
		return $this->addAnotherBlock('url');
	}

	/**
	 * Clicks the + issue button and waits for the screen to update
	 * 
	 * @return \Openbuildings\Spiderling\Node
	 */
	protected function addAnotherIssue()
	{
		return $this->addAnotherBlock('issue');
	}

	/**
	 * Generic helper to add a URL or issue block via a '+' button
	 * 
	 * @param string $type
	 * @return \Openbuildings\Spiderling\Node
	 */
	protected function addAnotherBlock($type)
	{
		$oldCount = count($this->all($selector = "#edit-report .{$type}-group"));
		$fluid = $this->
			find($selector)->
				click_button('+')->
			end();

		// Let's see how long we needed to wait
		$time = microtime(true);
		$ok = $this->waitForSelectorCount($selector, $oldCount + 1);
		$elapsedTime = round(microtime(true) - $time, 2);
		$this->assertTrue(
			$ok, 
			"Wait $elapsedTime seconds for another $type to be added"
		);

		return $fluid;		
	}

	/**
	 * Sets the nth URL input (zero-based)
	 * 
	 * @param integer $ord
	 * @param string $url
	 */
	protected function setSpecificUrl($ord, $url)
	{
		$this->setSpecificBlockInputValue('url', $ord - 1, $url);
	}

	/**
	 * Sets the nth issue resolved date (zero-based)
	 * 
	 * @param integer $ord
	 * @param string $resolvedDate
	 */
	protected function setSpecificIssueResolvedDate($ord, $resolvedDate)
	{
		$this->setSpecificBlockInputValue('issue', $ord, $resolvedDate);
	}

	/**
	 * Sets an input control
	 * 
	 * @param string $type
	 * @param integer $ord
	 * @param string $value
	 */
	protected function setSpecificBlockInputValue($type, $ord, $value)
	{
		$blocks = $this->all("#edit-report div.{$type}-group input");
		$blocks[$ord]->set($value);
	}

	/**
	 * Gets the usual start URL for tests in this class
	 * 
	 * @return string
	 */
	protected function pageUrl()
	{
		return self::DOMAIN . '/report/new';
	}
}