<?php

namespace Awooga\Testing\Unit;

class SearcherTest extends TestCase
{
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

	public function testUrlIndexing()
	{
		$searcher = new SearcherTestHarness();

		// Checks that indexing a site will index the domain and other things too
		$this->assertEquals(
			array(
				'example.com',
				'example.com/thing',
				'http://example.com',
				'http://example.com/thing',
				'http://www.example.com/thing',
				'www.example.com',
				'www.example.com/thing',
			),
			$this->sortArray($searcher->getUrlsToIndex('http://www.example.com/thing'))
		);
	}

	protected function sortArray(array $array)
	{
		sort($array);

		return $array;
	}
}