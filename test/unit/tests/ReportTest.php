<?php

namespace Awooga\Testing;

class ReportTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$root = $this->getProjectRoot();

		require_once $root . '/src/classes/Report.php';
		require_once $root . '/src/classes/TrivialException.php';
		require_once $root . '/test/unit/classes/ReportTestChild.php';
	}

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
		$urls = array(
			'',
			'http://example.com/two',
		);
		$report->setUrl($urls);
	}

	/**
	 * Make sure non-strings in URL arrays are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetArrayContainingUrlsOfWrongType()
	{
		$report = new ReportTestChild(1);
		$urls = array(
			'http://example.com/something',
			5,
		);
		$report->setUrl($urls);
		
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
		$this->assertEquals($notifiedDate, $report->getAuthorNotifiedDateAsSqlPublic());
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

	public function testSave()
	{
		$this->buildDatabase();
	}

	protected function buildDatabase()
	{
		$this->runSql($this->getProjectRoot() . '/test/build/init.sql');
		$this->runSql($this->getProjectRoot() . '/build/create.sql');
	}

	protected function runSql($sqlPath)
	{
		$sql = file_get_contents($sqlPath);

		// Connect to the database
		// @todo Pull this from env config
		$dsn = 'mysql:dbname=awooga_test;host=localhost;username=awooga_user_test;password=password';
		$pdo = new \PDO($dsn, 'awooga_user_test', 'password');
		$rows = $pdo->exec($sql);

		if ($rows === false)
		{
			print_r($pdo->errorInfo());
			throw new \Exception(
				"Could not initialise the database"
			);
		}		
	}

	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../../..');
	}
}