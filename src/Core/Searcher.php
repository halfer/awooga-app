<?php

namespace Awooga\Core;

class Searcher
{
	protected $index;
	protected $logger;

	use \Awooga\Core\Database;

	public function connect($indexPath)
	{
		// The mount script creates the index folder for us, so we need to check to see if
		// there is anything in it to be sure it is created.
		$exists = false;
		if (file_exists($indexPath))
		{
			$files = glob($indexPath . '/*');
			$exists = count($files) > 0;
		}

		// Create index
		if ($exists)
		{
			$this->index = \ZendSearch\Lucene\Lucene::open($indexPath);
		}
		else
		{
			$this->index = \ZendSearch\Lucene\Lucene::create($indexPath);
		}
	}

	/**
	 * Gets the current Zend search class
	 * 
	 * @return ZendSearch\Lucene\Index
	 * @throws \Exception
	 */
	protected function getIndex()
	{
		if (!$this->index)
		{
			throw new \Exception();
		}

		return $this->index;
	}

	// @todo Swap the ->index to ->getIndex, so we get exception protection
	public function index(array $report, $urls, array $issues)
	{
		// Compile the issues HTML
		$issuesHtml = '';
		foreach ($issues as $issue)
		{
			$issuesHtml .= $issue['description_html'];
		}
		$html = $report['description_html'] . $issuesHtml;

		// Let's delete this item first (and any dups, which should not exist)
		$foundDocs = $this->index->find('pk:' . $report['id']);
		if (count($foundDocs) > 1)
		{
			// @todo Reset log level
			$this->log("Warning, deleting more than one doc", 1);
		}
		foreach ($foundDocs as $foundDoc)
		{
			$this->index->delete($foundDoc->id);
		}

		// Add in HTML
		$doc = \ZendSearch\Lucene\Document\HTML::loadHtml($html);

		// Add useful fields
		$doc->addField(\ZendSearch\Lucene\Document\Field::keyword('pk', $report['id']));
		$doc->addField(\ZendSearch\Lucene\Document\Field::text('title', $report['title']));

		// Add URLs
		foreach ($urls as $ord => $url)
		{
			$keyName = 'url' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $url));
		}

		// Add issue keywords
		foreach ($issues as $ord => $issue)
		{
			$keyName = 'issue' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $issue['issue_cat_code']));
		}

		$this->getIndex()->addDocument($doc);
		// @todo Add logger
		$this->log("Added report " . $report['id'], 1);
	}

	/**
	 * Filter search before passing query to Zend Lucene
	 * 
	 * Zend Lucene will misinterpret URLs as meaning a special command: prefix, so we use
	 * a quoting device to prevent that here.
	 * 
	 * @param string $query
	 */
	public function search($query)
	{
		return $this->searchWithZend($this->quoteUrls($query));
	}

	/**
	 * Quotes any unquoted URLs within a string
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function quoteUrls($string)
	{
		$words = $this->quotedExplode($string);

		// If a word starts with a protocol, and is not quoted, quote it
		foreach ($words as $ord => $word)
		{
			$isUrl = (bool) preg_match('#^[a-z]{1,10}://#', $word);
			if ($isUrl)
			{
				$words[$ord] = '"' . $word . '"';
			}
		}

		return implode(' ', $words);
	}

	/**
	 * Explodes on space, except where a phrase is quoted
	 * 
	 * @param string $string
	 * @return array
	 */
	protected function quotedExplode($string)
	{
		$quotesMode = false;
		$pieces = array();
		$pos = 0;
		while ($pos < strlen($string))
		{
			if (substr($string, $pos, 1) == '"')
			{
				$quotesMode = !$quotesMode;
			}

			if (!$quotesMode && substr($string, $pos, 1) == ' ')
			{
				// Don't add empty pieces to the list
				$piece = substr($string, 0, $pos);
				if ($piece)
				{
					$pieces[] = $piece;
				}
				$string = substr($string, $pos + 1);
				$pos = 0;
			}
			
			$pos++;
		}

		// Add remainder as a piece
		if ($string)
		{
			$pieces[] = $quotesMode ? $string : ltrim($string);
		}

		return $pieces;
	}

	protected function searchWithZend($query)
	{
		// Save memory and make it snappy
		\ZendSearch\Lucene\Lucene::setResultSetLimit(200);

		return $this->getIndex()->find($query);		
	}

	/**
	 * Logs a message with the specified level
	 * 
	 * @todo Needs to be implemented properly
	 * 
	 * @param string $message
	 * @param integer $level
	 */
	protected function log($message, $level)
	{
		//echo $message . "\n";
	}
}