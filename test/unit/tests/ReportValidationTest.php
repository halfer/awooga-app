<?php

namespace Awooga\Testing\Unit;

class ReportValidationTest extends TestCase
{
	/**
	 * A test to check that setting a string title is OK
	 */
	public function testSetTitle()
	{
		$report = $this->getDummyReport();
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
		$this->getDummyReport()->setTitle(null);
	}

	/**
	 * Make sure null is rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testTitleOfBadType()
	{
		$this->getDummyReport()->setTitle(null);
	}

	/**
	 * Make sure non-string, non-null types are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testTitleOfBadTypeAgain()
	{
		$this->getDummyReport()->setTitle(new \stdClass());
	}

	/**
	 * Make sure a URL string can be set
	 */
	public function testSetUrlString()
	{
		$report = $this->getDummyReport();
		$url = 'http://example.com/thing';
		$report->setUrl($url);

		$this->assertEquals($url, $report->getUrl());
	}

	/**
	 * Check to ensure URLs can be set
	 */
	public function testSetGoodUrlArray()
	{
		$report = $this->getDummyReport();
		$urls = array(
			'http://example.com/one',
			'https://example.com/two',
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
		$this->getDummyReport()->setUrl(
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
		$this->getDummyReport()->setUrl(
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
		$this->getDummyReport()->setUrl(
			array(
				'http://example.com/something',
				'http://example.com/something',
			)
		);
	}

	/**
	 * Make sure unrecognised protocols are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testUnrecognisedProtocolUrl()
	{
		$this->getDummyReport()->setUrl('ftp://example.com/');		
	}

	/**
	 * Checks the report can accept and store a description string
	 */
	public function testSetGoodDescription()
	{
		$report = $this->getDummyReport();
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
		$this->getDummyReport()->setDescription(null);
	}

	/**
	 * Make sure non-string descriptions are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetDescriptionOfBadType()
	{
		$this->getDummyReport()->setDescription(6);
	}

	public function testSetGoodIssues()
	{
		// We need the database to access the issue codes
		$report = $this->buildDatabaseAndGetReport();

		$issues = array(
			array(
				'issue_cat_code' => 'xss'
			),
			array(
				'issue_cat_code' => 'sql-injection',
				'description' => 'A valid description string',
			),
			array(
				'issue_cat_code' => 'variable-injection',
				'resolved_at' => '2015-01-11',
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
		$this->getDummyReport()->setIssues(null);
	}

	/**
	 * Make sure null issues are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetEmptyIssueDescription()
	{
		$this->getDummyReport()->setIssues(array());
	}

	/**
	 * Make sure categorised duplicate issues are rejected
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetArrayContainingDuplicateIssues()
	{
		$this->checkMultipleIssueCodes('xss');
	}

	/**
	 * Make sure multiple uncategorised issues are accepted
	 */
	public function testSetArrayContainingMultipleUncategorisedIssues()
	{
		$this->checkMultipleIssueCodes('uncategorised');
	}

	protected function checkMultipleIssueCodes($code)
	{
		$this->buildDatabaseAndGetReport()->setIssues(
			array(
				array('issue_cat_code' => $code, 'description' => 'Description goes here', ),
				array('issue_cat_code' => $code, 'description' => 'Different description does not matter', ),
			)
		);		
	}

	/**
	 * Check that all these issue codes are regarded as valid
	 */
	public function testValidIssueCatCodes()
	{
		// We need the database to access the issue codes
		$report = $this->buildDatabaseAndGetReport();

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
		// We need the database to access the issue codes
		$issues = array(
			array('issue_cat_code' => 'this-does-not-exist', ),
		);
		$this->buildDatabaseAndGetReport()->setIssues($issues);
	}

	public function testIssueValidResolvedDate()
	{
		$report = $this->checkIssueDateResolvedValidity('2014-12-21');
		$this->assertTrue(
			isset($report->getIssues()[0]['resolved_at']),
			"Ensure the issue has a resolution date"
		);
	}

	/**
	 * Check that an invalid resolution date throws an exception
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testIssueSlightlyInvalidResolvedDate()
	{
		// Here the day and month are transposed
		$this->checkIssueDateResolvedValidity('2014-21-12');
	}

	/**
	 * Check that a very invalid resolution date throws an exception
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testIssueReallyInvalidResolvedDate()
	{
		$this->checkIssueDateResolvedValidity('nonsense');
	}

	/**
	 * Helper method to test whether good and bad date formats can be told apart
	 * 
	 * @param string $strDate
	 */
	protected function checkIssueDateResolvedValidity($strDate)
	{
		// We need the database to access the issue codes
		$report = $this->buildDatabaseAndGetReport();

		$issues = array(
			array(
				'issue_cat_code' => 'xss',
				'description' => 'The author has switched from the deprecated mysql library to PDO, and all queries now use parameterisation!',
				'resolved_at' => $strDate,
			)
		);
		$report->setIssues($issues);

		return $report;
	}

	/**
	 * Sets a notified date that should be accepted and recorded
	 */
	public function testSetGoodAuthorNotifiedDate()
	{
		$report = $this->getDummyReport();
		$notifiedDate = '2014-11-18';
		$report->setAuthorNotifiedDate($notifiedDate);
		$this->assertEquals($notifiedDate, $report->getAuthorNotifiedDateAsString());
	}

	/**
	 * Sets a notified date, of the wrong type, that should be thrown out
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetAuthorNotifiedDateWrongType()
	{
		$this->getDummyReport()->setAuthorNotifiedDate(new \stdClass());
	}

	/**
	 * Sets a notified date, of the wrong string format, which should be thrown out
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetAuthorNotifiedDateWrongFormat()
	{
		$this->getDummyReport()->setAuthorNotifiedDate('18-11-2014');
	}

	/**
	 * Sets a notified date, with date and month transposed, which should be thrown out
	 * 
	 * @expectedException \Awooga\Exceptions\TrivialException
	 */
	public function testSetAuthorNotifiedSlightlyWrongFormat()
	{
		$this->getDummyReport()->setAuthorNotifiedDate('2014-18-11');		
	}

	/**
	 * Ensure null is a valid value for author-notified-date
	 */
	public function testSetNullAuthorNotifiedDate()
	{
		$report = $this->getDummyReport();
		$report->setAuthorNotifiedDate(null);
		$this->assertNull(
			$report->getAuthorNotifiedDateAsString(),
			"Check author notified date can be set to null"
		);
	}



	/**
	 * Returns a report to test against
	 * 
	 * @todo Rename to getDummyRepoBasedReport?
	 * 
	 * @return \Awooga\Testing\Unit\ReportTestHarness
	 */
	protected function getDummyReport()
	{
		return new ReportTestHarness(1);
	}

	/**
	 * Returns a report to test against
	 * 
	 * @return \Awooga\Testing\Unit\ReportTestHarness
	 */
	protected function getDummyUserBasedReport()
	{
		return new ReportTestHarness(null);		
	}
}