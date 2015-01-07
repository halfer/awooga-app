<?php

namespace Awooga\Testing\Unit;

use \Awooga\Core\Report;

class ReportWriteTest extends TestCase
{
	/**
	 * Saves a report
	 * 
	 * @throws \Exception
	 */
	public function testSaveNewReport()
	{
		$report = $this->buildDatabaseAndGetReport();

		$report->setTitle($title = 'Example title');
		$report->setDescription($description = 'Example description');
		$report->setUrl(
			$urls = array('http://example.com/one', 'http://example.com/two', )
		);
		$report->setIssues(
			$issues = array(
				array('issue_cat_code' => 'sql-injection', 'resolved_at' => null, ),
				array('issue_cat_code' => 'xss', 'resolved_at' => '2014-10-20', ),
			)
		);
		$reportId = $report->save();
		
		// Check report ID
		$pdo = $this->getDriver();
		$this->assertEquals(
			1,
			$this->fetchColumn(
				$pdo,
				"SELECT 1 FROM report WHERE id = :report_id",
				array(':report_id' => $reportId, )
			),
			"Check report ID is generated OK"
		);

		// Check issues by counting reports either by repo or user, as appropriate
		$repoId = $report->getProperty('repoId');
		$userId = $report->getProperty('userId');
		$statement = $this->runStatementWithException(
			$pdo,
			$this->getRetrieveIssuesSql((bool) $repoId),
			$repoId ? array(':repo_id' => $repoId, ) : array(':user_id' => $userId, )
		);

		$issuesData = $statement->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($issuesData as $issueData)
		{
			$this->assertEquals($issueData['title'], $title);
			$this->assertEquals($issueData['description'], $description);
			// Check issues are recorded OK
			$issue = current($issues);
			$this->assertEquals($issue['issue_cat_code'], $issueData['issue_code']);
			$this->assertEquals($issue['resolved_at'], $issueData['issue_resolved_at']);
			next($issues);
		}

		// Check urls by counting reports either by repo or user, as appropriate
		$statement2 = $this->runStatementWithException(
			$pdo,
			$this->getRetrieveUrlsSql((bool) $repoId),
			$repoId ? array(':repo_id' => $repoId, ) : array(':user_id' => $userId, )
		);

		$urlsData = $statement2->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($urlsData as $urlData)
		{
			$this->assertEquals($urlData['title'], $title);
			$this->assertEquals($urlData['description'], $description);
			$this->assertEquals(current($urls), $urlData['url']);
			next($urls);
		}
	}

	/**
	 * 
	 * @param boolean $byRepo
	 * @return string
	 */
	protected function getRetrieveIssuesSql($byRepo)
	{
		$filter = $byRepo ? 'r.repository_id = :repo_id' : 'r.user_id = :user_id';

		return "
			SELECT
				r.*,
				ri.description issue_description,
				ri.resolved_at issue_resolved_at,
				i.code issue_code
			FROM
				report r
				INNER JOIN report_issue ri ON (r.id = ri.report_id)
				INNER JOIN issue i ON (ri.issue_id = i.id)
			WHERE
				{$filter}
			ORDER BY
				i.code				
		";
	}

	/**
	 * 
	 * @param boolean $byRepo
	 * @return string
	 */
	protected function getRetrieveUrlsSql($byRepo)
	{
		$filter = $byRepo ? 'r.repository_id = :repo_id' : 'r.user_id = :user_id';

		return "
			SELECT
				r.*,
				u.url
			FROM
				report r
				INNER JOIN resource_url u ON (r.id = u.report_id)
			WHERE
				{$filter}
			ORDER BY
				u.url
		";
	}

	/**
	 * 
	 */
	public function testSaveWithNullAuthorNotificationDate()
	{
		$report = $this->buildDatabaseAndGetReport();

		// Set some fields
		$report->setTitle('Example title');
		$report->setDescription('Example description');
		$report->setUrl(
			array('http://example.com/one', 'http://example.com/two', )
		);
		$report->setIssues(
			array(
				array('issue_cat_code' => 'sql-injection', ),
				array(
					'issue_cat_code' => 'xss',
					'resolved_at' => '2014-11-18'
				),
			)
		);
		$report->save();

		// Check date is null
		$sql = "
			SELECT 1 FROM report
			WHERE
				repository_id = :repo_id
				AND author_notified_at IS NULL
		";
		$statement=  $this->runStatement(
			$this->getDriver(),
			$sql,
			array('repo_id' => $report->getProperty('repoId'), )
		);
		$this->assertEquals(1, $statement->rowCount());
	}

	/**
	 * Saving without a title should not be disallowed
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSaveNewReportWithNoTitle()
	{
		$this->trySavingNewReportWithMissingField('title');
	}

	/**
	 * Saving without a description should not be disallowed
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSaveNewReportWithNoDescription()
	{
		$this->trySavingNewReportWithMissingField('description');
	}

	/**
	 * Saving without urls should not be disallowed
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSaveNewReportWithNoUrls()
	{
		$this->trySavingNewReportWithMissingField('urls');
	}

	/**
	 * Saving without issues should not be disallowed
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSaveNewReportWithNoIssues()
	{
		$this->trySavingNewReportWithMissingField('issues');
	}

	/**
	 * 
	 * 
	 * @param string $field
	 */
	protected function trySavingNewReportWithMissingField($field)
	{
		$report = $this->buildDatabaseAndGetReport();

		// Skip one of the fields here
		if ($field != 'title')
		{
			$report->setTitle('Example title');
		}
		if ($field != 'description')
		{
			$report->setDescription('Example description');
		}
		if ($field != 'urls')
		{
			$report->setUrl(
				array('http://example.com/one', 'http://example.com/two', )
			);
		}
		if ($field != 'issues')
		{
			$report->setIssues(
				array(
					array('issue_cat_code' => 'sql-injection', ),
					array('issue_cat_code' => 'xss', ),
				)
			);
		}

		$report->save();
	}

	/**
	 * Checks that two reports in the same repo and with the same URL are regarded as the same
	 */
	public function testUpdateOldRepoReport()
	{
		// Create two reports linked to a repo
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);
		$report1 = new ReportTestHarness($repoId);
		$report2 = new ReportTestHarness($repoId);

		$this->checkUpdateOldReport($pdo, $report1, $report2);
	}

	/**
	 * Checks that two reports for the same user and with the same URL are regarded as the same
	 */
	public function testUpdateOldUserReport()
	{
		// Create two reports linked to a user
		$pdo = $this->getDriver();
		$userId = $this->buildDatabase($pdo, false, true);
		$report1 = new ReportTestHarness(null, $userId);
		$report2 = new ReportTestHarness(null, $userId);

		$this->checkUpdateOldReport($pdo, $report1, $report2);
	}

	/**
	 * Check that a repo/user and URL match updates a report, rather than creating a new one
	 * 
	 * @param \PDO $pdo
	 * @param Report $report
	 * @param Report $report2
	 * @throws \Exception
	 */
	protected function checkUpdateOldReport(\PDO $pdo, Report $report, Report $report2)
	{
		// Save a report with dummy data
		$report->setDriver($pdo);
		$this->setDummyReportData($report);
		$report->save();

		// Resave the +same+ report
		$report2->setDriver($pdo);
		$report2->setUrl($report->getUrl());
		// Change all the data here, it's still the same report
		$report2->setTitle($title = 'Different title');
		$report2->setDescription($description = 'Different description');
		$report2->setIssues(
			$issues = array(array('issue_cat_code' => 'sql-injection', ),)
		);
		$report2->save();

		// Get the reports for this repo/user as appropriate
		$repoId = $report->getProperty('repoId');
		$userId = $report->getProperty('userId');
		$statement = $this->runStatementWithException(
			$pdo,
			$this->getRetrieveIssuesSql((bool) $repoId),
			$repoId ? array(':repo_id' => $repoId, ) : array(':user_id' => $userId, )
		);

		// Ensure we only have one report
		$issuesData = $statement->fetchAll(\PDO::FETCH_ASSOC);
		$this->assertEquals(
			1,
			count($issuesData),
			"Check number of reports against this repo/user"
		);

		// Check the report was updated and not duplicated
		foreach ($issuesData as $issueData)
		{
			$this->assertEquals($issueData['title'], $title);
			$this->assertEquals($issueData['description'], $description);
			$issue = current($issues);
			$this->assertEquals($issue['issue_cat_code'], $issueData['issue_code']);
			next($issues);
		}
	}

	/**
	 * Saving a report with URLs that cross multiple reports must be disallowed
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testUrlArrayCannotUpdateMultipleReports()
	{
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);

		// Create a report
		$report = new ReportTestHarness($repoId);
		$report->setDriver($pdo);
		$this->setDummyReportData($report);
		$report->save();

		// Create another report
		$report2 = new ReportTestHarness($repoId);
		$report2->setDriver($pdo);
		$this->setDummyReportData($report2);
		$report2->setUrl($url2 = 'http://example.com/different');
		$report2->save();

		// Try to create a report that would span these two URLs
		$report3 = new ReportTestHarness($repoId);
		$report3->setDriver($pdo);
		$this->setDummyReportData($report3);
		$report3->setUrl(array($report->getUrl(), $url2));
		$report3->save();
	}

	public function testReportAttachedToUser()
	{
		$pdo = $this->getDriver();
		$userId = $this->buildDatabase($pdo, false, true);
		$report = new ReportTestHarness(null, $userId);
		$report->setDriver($pdo);
		$this->setDummyReportData($report);
		$reportId = $report->save();

		// Check it was written OK
		$actualTitle = $this->fetchColumn(
			$pdo,
			'SELECT title FROM report WHERE id = :report_id',
			array('report_id' => $reportId, )
		);
		$this->assertEquals(
			$report->getProperty('title'),
			$actualTitle,
			'Ensure report saves correctly for a user-based report'
		);
	}

	/**
	 * Check foreign key validation (with neither, which is invalid)
	 * 
	 * @expectedException \Awooga\Exceptions\SeriousException
	 */
	public function testReportCannotHaveUserAndRepo()
	{
		$report = new ReportTestHarness(null);
		$report->setDriver($this->getDriver());
		$this->setDummyReportData($report);
		$report->save();
	}

	/**
	 * Check foreign key validation (with both, which is invalid)
	 * 
	 * @expectedException \Awooga\Exceptions\SeriousException
	 */
	public function testReportMustHaveUserOrRepo()
	{
		$report = new ReportTestHarness(1, 1);
		$report->setDriver($this->getDriver());
		$this->setDummyReportData($report);
		$report->save();		
	}

	/**
	 * Helper method to fill in dummy data
	 * 
	 * @param Report $report
	 */
	protected function setDummyReportData(Report $report)
	{
		$report->setUrl('http://example.com');
		$report->setTitle('Title');
		$report->setDescription('Description');
		$report->setIssues(array(array('issue_cat_code' => 'xss', ),));
	}
}