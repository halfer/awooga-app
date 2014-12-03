<?php

namespace Awooga\Testing;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class SearcherTest extends TestCase
{
	/**
	 * Loads the classes we need
	 */
	public function setUp()
	{
		parent::setUp();

		$root = $this->getProjectRoot();
		require_once $root . '/test/unit/classes/SearcherTestHarness.php';
	}

	public function testQuotedExplode()
	{
		$searcher = new SearcherTestHarness();

		// Basic explode
		$this->assertEquals(
			array('one', 'two', ),
			$searcher->quotedExplode('one two')
		);

		// Basic quoting
		$this->assertEquals(
			array('"one two"', ),
			$searcher->quotedExplode('"one two"')
		);

		// Quoted URL plus keywords
		$this->assertEquals(
			array('"http://example.com"', 'one', 'two', ),
			$searcher->quotedExplode('"http://example.com" one two')
		);

		// Preceding spaces
		$this->assertEquals(
			array('one', 'two', ),
			$searcher->quotedExplode(' one two')
		);

		// Trailing spaces
		$this->assertEquals(
			array('one', 'two', ),
			$searcher->quotedExplode('one two ')
		);
	}

	public function testQuoteUrls()
	{
		$searcher = new SearcherTestHarness();

		// Simple pattern
		$this->assertEquals(
			'one two',
			$searcher->quoteUrls('one two')
		);

		// Pattern with URL at the end
		$this->assertEquals(
			'one "http://example.com"',
			$searcher->quoteUrls('one http://example.com')
		);

		// Pattern with URL at the start
		$this->assertEquals(
			'"http://example.com" two',
			$searcher->quoteUrls('http://example.com two')
		);

		// Pattern with URL in the middle
		$this->assertEquals(
			'one "http://example.com" two',
			$searcher->quoteUrls('one http://example.com two')
		);

		// Pattern with quoted URL at the end
		$this->assertEquals(
			'one "http://example.com"',
			$searcher->quoteUrls('one "http://example.com"')
		);

		// Pattern with quoted URL at the start
		$this->assertEquals(
			'"http://example.com" two',
			$searcher->quoteUrls('"http://example.com" two')
		);

		// Pattern with quoted URL in the middle
		$this->assertEquals(
			'one "http://example.com" two',
			$searcher->quoteUrls('one "http://example.com" two')
		);
	}
}