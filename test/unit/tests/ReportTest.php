<?php

namespace Awooga\Testing;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class ReportTest extends TestCase
{
	/**
	 * Set-up routine for every test method
	 */
	public function setUp()
	{
		$root = $this->getProjectRoot();

		require_once $root . '/src/classes/Report.php';
		require_once $root . '/src/classes/Exceptions/SeriousException.php';
		require_once $root . '/src/classes/Exceptions/TrivialException.php';
		require_once $root . '/test/unit/classes/ReportTestChild.php';
	}

	/**
	 * A test to check that setting a string title is OK
	 */
	public function testSetTitle()
	{
		$report = new ReportTestChild(1);
		$title = 'Set title';
		$report->setTitle($title);
		$this->assertEquals($title, $report->getProperty('title'));
	}

	/**
	 * Make sure null/empty titles are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testNoTitle()
	{
		$report = new ReportTestChild(1);
		$report->setTitle(null);
	}

	/**
	 * Make sure non-strings are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testTitleOfBadType()
	{
		$report = new ReportTestChild(1);
		$report->setTitle(null);
		$report->setTitle(new \stdClass());
	}

	/**
	 * Make sure a URL string can be set
	 */
	public function testSetUrlString()
	{
		$report = new ReportTestChild(1);
		$url = 'http://example.com/thing';
		$report->setUrl($url);

		$this->assertEquals($url, $report->getUrl());
	}

	/**
	 * Check to ensure URLs can be set
	 */
	public function testSetGoodUrlArray()
	{
		$report = new ReportTestChild(1);
		$urls = array(
			'http://example.com/one',
			'http://example.com/two',
		);
		$report->setUrl($urls);
		$this->assertEquals($urls, $report->getUrl());
	}

	/**
	 * Make sure empty strings in URL arrays are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetArrayContainingEmptyUrls()
	{
		$report = new ReportTestChild(1);
		$report->setUrl(
			array(
				'',
				'http://example.com/two',
			)
		);
	}

	/**
	 * Make sure non-strings in URL arrays are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetArrayContainingUrlsOfWrongType()
	{
		$report = new ReportTestChild(1);
		$report->setUrl(
			array(
				'http://example.com/something',
				5,
			)
		);
	}

	/**
	 * Make sure duplicate URLs are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetArrayContainingDuplicateUrls()
	{
		$report = new ReportTestChild(1);
		$report->setUrl(
			array(
				'http://example.com/something',
				'http://example.com/something',
			)
		);
	}

	/**
	 * Checks the report can accept and store a description string
	 */
	public function testSetGoodDescription()
	{
		$report = new ReportTestChild(1);
		$description = 'This is a description';
		$report->setDescription($description);
		$this->assertEquals($description, $report->getProperty('description'));
	}

	/**
	 * Make sure empty descriptions are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetEmptyDescription()
	{
		$report = new ReportTestChild(1);
		$report->setDescription(null);
	}

	/**
	 * Make sure non-string descriptions are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetDescriptionOfBadType()
	{
		$report = new ReportTestChild(1);
		$report->setDescription(6);		
	}

	public function testSetGoodIssues()
	{
		$report = new ReportTestChild(1);
		$issues = array(
			array(
				'issue_cat_code' => 'xss'
			),
			array(
				'issue_cat_code' => 'sql-injection',
				'description' => 'A valid description string',
			)
		);
		$report->setIssues($issues);
		$this->assertEquals($issues, $report->getProperty('issues'));
	}

	/**
	 * Make sure null issues are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetNullIssues()
	{
		$report = new ReportTestChild(1);
		$report->setIssues(null);
	}

	/**
	 * Make sure null issues are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetEmptyIssueDescription()
	{
		$report = new ReportTestChild(1);
		$report->setIssues(array());
	}

	/**
	 * Make sure duplicate issues are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetArrayContainingDuplicateIssues()
	{
		$report = new ReportTestChild(1);
		$report->setIssues(
			array(
				array('issue_cat_code' => 'xss', 'description' => 'Description goes here', ),
				array('issue_cat_code' => 'xss', 'description' => 'Different description does not matter', ),
			)
		);
	}

	/**
	 * Check that all these issue codes are regarded as valid
	 */
	public function testValidIssueCatCodes()
	{
		$report = new ReportTestChild(1);
		$issues = array(
			array('issue_cat_code' => 'xss', ),
			array('issue_cat_code' => 'sql-injection', ),
			array('issue_cat_code' => 'password-clear', ),
			array('issue_cat_code' => 'password-inadequate-hashing', ),
			array('issue_cat_code' => 'deprecated-library', ),
			array('issue_cat_code' => 'sql-needs-parameterisation', ),
			array('issue_cat_code' => 'uncategorised', ),
		);
		$report->setIssues($issues);
		$this->assertEquals($issues, $report->getProperty('issues'));
	}

	/**
	 * Check that an invalid code throws an exception
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testInvalidIssueCatCode()
	{
		$report = new ReportTestChild(1);
		$issues = array(
			array('issue_cat_code' => 'this-does-not-exist', ),
		);
		$report->setIssues($issues);
	}

	/**
	 * Sets a notified date that should be accepted and recorded
	 */
	public function testSetGoodAuthorNotifiedDate()
	{
		$report = new ReportTestChild(1);
		$notifiedDate = '2014-11-18';
		$report->setAuthorNotifiedDate($notifiedDate);
		$this->assertEquals($notifiedDate, $report->getAuthorNotifiedDateAsStringPublic());
	}

	/**
	 * Sets a notified date, of the wrong type, that should be thrown out
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetAuthorNotifiedDateWrongType()
	{
		$report = new ReportTestChild(1);
		$report->setAuthorNotifiedDate(new \stdClass());
	}

	/**
	 * Sets a notified date, of the wrong string format, that should be thrown out
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetAuthorNotifiedDateWrongFormat()
	{
		$report = new ReportTestChild(1);
		$report->setAuthorNotifiedDate('18/11/2014');
	}

	public function testSaveNewReport()
	{
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);
		$report = new ReportTestChild($repoId);
		$report->setDriver($pdo);

		$report->setTitle($title = 'Example title');
		$report->setDescription($description = 'Example description');
		$report->setUrl(
			$urls = array('http://example.com/one', 'http://example.com/two', )
		);
		$report->setIssues(
			$issues = array(
				array('issue_cat_code' => 'sql-injection', ),
				array('issue_cat_code' => 'xss', ),
			)
		);
		$report->save();

		// Check issues
		$statement = $pdo->prepare($this->getRetrieveIssuesSql());
		$ok = $statement->execute(array(':repo_id' => $repoId, ));
		if ($ok === false)
		{
			throw new \Exception(
				"Database call failed:" . print_r($statement->errorInfo(), true)
			);
		}

		$issuesData = $statement->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($issuesData as $issueData)
		{
			$this->assertEquals($issueData['title'], $title);
			$this->assertEquals($issueData['description'], $description);
			$issue = current($issues);
			$this->assertEquals($issue['issue_cat_code'], $issueData['issue_code']);
			next($issues);
		}

		// Check urls
		$statement2 = $pdo->prepare($this->getRetrieveUrlsSql());
		$ok2 = $statement2->execute(array(':repo_id' => $repoId, ));
		if ($ok2 === false)
		{
			throw new \Exception(
				"Database call failed:" . print_r($statement->errorInfo(), true)
			);
		}

		$urlsData = $statement2->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($urlsData as $urlData)
		{
			$this->assertEquals($urlData['title'], $title);
			$this->assertEquals($urlData['description'], $description);
			$this->assertEquals(current($urls), $urlData['url']);
			next($urls);
		}
	}

	protected function getRetrieveIssuesSql()
	{
		return "
			SELECT
				r.*,
				ri.description issue_description,
				i.code issue_code
			FROM
				report r
				INNER JOIN report_issue ri ON (r.id = ri.report_id)
				INNER JOIN issue i ON (ri.issue_id = i.id)
			WHERE
				r.repository_id = :repo_id
			ORDER BY
				i.code				
		";
	}

	protected function getRetrieveUrlsSql()
	{
		return "
			SELECT
				r.*,
				u.url
			FROM
				report r
				INNER JOIN resource_url u ON (r.id = u.report_id)
			WHERE
				r.repository_id = :repo_id
			ORDER BY
				u.url
		";
	}

	public function testSaveWithNullAuthorNotificationDate()
	{
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);
		$report = new ReportTestChild($repoId);
		$report->setDriver($pdo);

		// Set some fields
		$report->setTitle('Example title');
		$report->setDescription('Example description');
		$report->setUrl(
			array('http://example.com/one', 'http://example.com/two', )
		);
		$report->setIssues(
			array(
				array('issue_cat_code' => 'sql-injection', ),
				array('issue_cat_code' => 'xss', ),
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
		$statement = $pdo->prepare($sql);
		$statement->execute(array('repo_id' => $repoId, ));
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

	protected function trySavingNewReportWithMissingField($field)
	{
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);
		$report = new ReportTestChild($repoId);
		$report->setDriver($pdo);

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
	 * Check that a repo ID and URL match updates a report, rather than creating a new one
	 * 
	 * @throws \Exception
	 */
	public function testUpdateOldReport()
	{
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);

		// Create a report
		$report = new ReportTestChild($repoId);
		$report->setDriver($pdo);
		$report->setUrl('http://example.com');
		$report->setTitle('Title');
		$report->setDescription('Description');
		$report->setIssues(array(array('issue_cat_code' => 'xss', ),));
		$report->save();

		// Resave the +same+ report
		$report2 = new ReportTestChild($repoId);
		$report2->setDriver($pdo);
		$report2->setUrl($url = 'http://example.com');
		// Change all the data here, it's still the same report
		$report2->setTitle($title = 'Different title');
		$report2->setDescription($description = 'Different description');
		$report2->setIssues(
			$issues = array(array('issue_cat_code' => 'sql-injection', ),)
		);
		$report2->save();

		// Get the reports for this repo
		$statement = $pdo->prepare($this->getRetrieveIssuesSql());
		$ok = $statement->execute(array(':repo_id' => $repoId, ));
		if ($ok === false)
		{
			throw new \Exception(
				"Database call failed:" . print_r($statement->errorInfo(), true)
			);
		}

		// Ensure we only have one report
		$issuesData = $statement->fetchAll(\PDO::FETCH_ASSOC);
		$this->assertEquals(
			1,
			count($issuesData),
			"Check number of reports in this repo"
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
		$report = new ReportTestChild($repoId);
		$report->setDriver($pdo);
		$report->setUrl($url1 = 'http://example.com');
		$report->setTitle('Title');
		$report->setDescription('Description');
		$report->setIssues(array(array('issue_cat_code' => 'xss', ),));
		$report->save();

		// Create another report
		$report2 = new ReportTestChild($repoId);
		$report2->setDriver($pdo);
		$report2->setUrl($url2 = 'http://example.com/different');
		$report2->setTitle('Title');
		$report2->setDescription('Description');
		$report2->setIssues(array(array('issue_cat_code' => 'xss', ),));
		$report2->save();

		// Try to create a report that would span these two URLs
		$report3 = new ReportTestChild($repoId);
		$report3->setDriver($pdo);
		$report3->setUrl(array($url1, $url2));
		$report3->setTitle('Title');
		$report3->setDescription('Description');
		$report3->setIssues(array(array('issue_cat_code' => 'xss', ),));
		$report3->save();
	}
}