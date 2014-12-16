<?php

namespace Awooga\Testing\Unit;

class SearcherTestHarness extends \Awooga\Core\Searcher
{
	/**
	 * Public entry point for the purpose of testing
	 * 
	 * @param string $string
	 */
	public function quotedExplode($string)
	{
		return parent::quotedExplode($string);
	}

	/**
	 * Public entry point for the purpose of testing
	 * 
	 * @param string $string
	 */
	public function quoteUrls($string)
	{
		return parent::quoteUrls($string);
	}

	/**
	 * Public entry point for the purpose of testing
	 * 
	 * @param string $url
	 */
	public function getUrlsToIndex($url)
	{
		return parent::getUrlsToIndex($url);
	}
}