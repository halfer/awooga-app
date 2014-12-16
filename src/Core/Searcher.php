<?php

namespace Awooga\Core;

class Searcher
{
	protected $verbose;
	protected $index;

	use \Awooga\Core\Database;

	public function __construct($verbose = false)
	{
		$this->verbose = $verbose;
	}

	/**
	 * Opens or creates the search index folder
	 * 
	 * @param string $indexPath
	 */
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
	 * @return \ZendSearch\Lucene\Index
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

	/**
	 * Adds report data to the text search index
	 * 
	 * @todo Swap the ->index to ->getIndex, so we get exception protection
	 * 
	 * @param array $report
	 * @param array $urls
	 * @param array $issues
	 */
	public function index(array $report, array $urls, array $issues)
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
			$this->log("Warning, deleting more than one instance of report " . $report['id']);
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
		$urlId = 0;
		foreach ($urls as $baseUrl)
		{
			// For each URL, add the domain and the non-www forms etc.
			foreach ($this->getUrlsToIndex($baseUrl) as $url)
			{
				$keyName = 'url' . $urlId++;
				$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $url));
			}
		}

		// Add issue keywords
		foreach ($issues as $ord => $issue)
		{
			$keyName = 'issue' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $issue['issue_cat_code']));
		}

		$this->getIndex()->addDocument($doc);
		$this->log("Added report " . $report['id']);
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
	 * For a given URL, returns an array of things to index
	 * 
	 * @param string $url
	 */
	protected function getUrlsToIndex($url)
	{
		$parts = parse_url($url);
		$scheme = isset($parts['scheme']) ? $parts['scheme'] : null;
		$host = isset($parts['host']) ? $parts['host'] : null;
		$path = isset($parts['path']) ? $parts['path'] : null;

		// We'll always index the URL itself
		$urls = array($url, );

		// Add these if the elements exist
		if ($host)
		{
			// Add the host on its own
			$urls[] = $host;

			if ($path)
			{
				$urls[] = $host . $path;
			}

			// Is there a www part?
			$count = 0;
			$noWwwHost = preg_replace('/^www\./', '', $host, 1, $count);
			if ($count)
			{
				// Add the non-www host on its own
				$urls[] = $noWwwHost;

				if ($scheme)
				{
					$urls[] = $scheme . '://' . $noWwwHost;

					if ($path)
					{
						$urls[] = $scheme . '://' . $noWwwHost . $path;
					}
				}

				if ($path)
				{
					$urls[] = $noWwwHost . $path;
				}
			}
		}

		return $urls;
	}

	/**
	 * Quotes any unquoted URLs within a string
	 * 
	 * Potentially we could identify a URL as anything with dots and slashes in it, but this
	 * mix will do for now - either starts with a protocol or "www.".
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function quoteUrls($string)
	{
		$words = $this->quotedExplode($string);

		// If a word starts with a protocol or www, and is not quoted, quote it
		foreach ($words as $ord => $word)
		{
			$isUrl = (bool) preg_match('#^([a-z]{1,10}://|www\.)#', $word);
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

	/**
	 * Convenience method to set search limit and run search
	 * 
	 * @param string $query
	 */
	protected function searchWithZend($query)
	{
		// Save memory and make it snappy
		\ZendSearch\Lucene\Lucene::setResultSetLimit(200);

		return $this->getIndex()->find($query);		
	}

	/**
	 * Logs a message with the specified level
	 * 
	 * @param string $message
	 */
	protected function log($message)
	{
		if ($this->verbose)
		{
			echo $message . "\n";
		}
	}
}