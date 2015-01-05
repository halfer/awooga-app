<?php

namespace Awooga\Testing\Browser;

use \Openbuildings\Spiderling\Node;

class ReportTest extends TestCase
{
	/**
	 * Tests everything in a repo report
	 * 
	 * @driver phantomjs
	 */
	public function testRepoReport()
	{
		$page = $this->visit(self::DOMAIN . '/report/7');

		// Check URL
		$this->assertEquals(
			'http://www.learn2crack.com/2013/08/develop-android-login-registration-with-php-mysql.html/4',
			$page->find('table#report tbody tr:first-child td:last-child')->text()
		);

		// Check issues
		$issues = array(
			2 => array('sql-injection', '(No comments added)'),
			3 => array('password-inadequate-hashing', 'SHA1/base64/salt home-made algorithm not a substitute for password_hash().'),
			4 => array('deprecated-library', '(No comments added)'),
		);
		$offset = 0;
		foreach ($issues as $rowId => $issueData)
		{
			// Issue tag
			$colIssue = 2 - $offset;
			$this->assertEquals(
				$issueData[0],
				$page->find("table#report tbody tr:nth-child($rowId) td:nth-child($colIssue)")->text()
			);
			// Solved status
			$colSolved = 3 - $offset;
			$this->assertEquals(
				'Unresolved',
				$page->find("table#report tbody tr:nth-child($rowId) td:nth-child($colSolved)")->text()
			);
			// Optional comments
			$colComment = 4 - $offset;
			$this->assertEquals(
				$issueData[1],
				$page->find("table#report tbody tr:nth-child($rowId) td:nth-child($colComment)")->text()
			);

			// This device helps calculate the column offset caused by the header spanning
			$offset = 1;
		}

		// Check description, using a contains test for brevity
		$this->assertContains(
			'The usual SQL injection flaws in this one',
			$page->find('table#report tbody tr:nth-child(5) td:last-child')->text()
		);

		// Check repo owner
		$this->checkReportSource($page, 'Repo: 1');
	}

	/**
	 * Tests that a user report has the correct source string
	 * 
	 * (Everything else on this page is the same as a repo report)
	 * 
	 * @driver phantomjs
	 */
	public function testUserReport()
	{
		$page = $this->visit(self::DOMAIN . '/report/26');

		// Check user owner
		$this->checkReportSource($page, 'User: https://github.com/halfer');
	}

	/**
	 * A utility method to check the report source string
	 * 
	 * @param Node $page
	 * @param string $expectedText
	 */
	protected function checkReportSource(Node $page, $expectedText)
	{
		$this->assertEquals(
			$expectedText,
			$page->find('table#report tbody tr:last-child td:last-child')->text()
		);
	}

	/**
	 * Ensure the edit link is displayed/hidden according to logged-in status
	 * 
	 * @driver phantomjs
	 */
	public function testEditLinkVisibleOnlyWhenLoggedIn()
	{
		// Check that a report owned by a user shows an edit link
		$this->loginTestUser();
		$reportHeader = $this->visit(self::DOMAIN . '/report/24')->find('#report-header')->text();
		$this->assertContains('Edit report', $reportHeader);
	}

	/**
	 * Ensure the edit link is hidden on other users' reports
	 * 
	 * @driver phantomjs
	 */
	public function testEditLinkHiddenOnAnotherUsersReport()
	{
		// Check when signed out
		$this->checkEditLinkNotPresent();

		// Log in, then check again
		$this->loginTestUser();
		$this->checkEditLinkNotPresent();
	}

	/**
	 * Checks that a user and a repo report are not editable
	 */
	protected function checkEditLinkNotPresent()
	{
		// Test another user's report (26) and a repo report (7)
		foreach (array(26, 7) as $reportId)
		{
			$reportHeader = $this->
				visit(self::DOMAIN . '/report/' . $reportId)->
				find('#report-header')->text();
			$this->assertNotContains('Edit report', $reportHeader);
		}		
	}

	protected function loginTestUser()
	{
		// Logon and then check that it worked
		$this->visit(self::DOMAIN . '/auth?provider=test');
		$this->assertEquals('Logout testuser', $this->find('#auth-status')->text());
	}
}
