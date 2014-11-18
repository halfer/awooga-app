<?php

namespace Awooga\Testing;

class ReportTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$root = realpath(__DIR__ . '/../../..');

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

	public function testSetDescriptionOfBadType()
	{
		$report = new ReportTestChild(1);
		$report->setDescription(6);		
	}

	public function testSetGoodIssues()
	{
		
	}

	public function testSetEmptyIssues()
	{
		
	}

	public function testSetGoodAuthorNotifiedDate()
	{
		
	}

	public function testSetBadAuthorNotifiedDate()
	{
		
	}

	public function testSave()
	{
		
	}
}