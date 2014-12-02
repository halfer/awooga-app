<?php

namespace Awooga\Core;

class Searcher
{
	protected $index;
	protected $report;
	protected $urls;
	protected $issues;
	protected $logger;

	use \Awooga\Core\Database;

	public function __construct($indexPath)
	{
		// Create index
		if (file_exists($indexPath))
		{
			$this->index = ZendSearch\Lucene\Lucene::open($indexPath);
		}
		else
		{
			$this->index = ZendSearch\Lucene\Lucene::create($indexPath);
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

	public function setReport(array $report)
	{
		$this->report = $report;
	}

	/**
	 * Sets the URL(s)
	 * 
	 * We can't typehint on array as it can be a string
	 * 
	 * @param type $urls
	 */
	public function setUrls($urls)
	{
		$this->urls = $urls;
	}

	public function setIssues(array $issues)
	{
		$this->issues = $issues;
	}

	public function index()
	{
		// Compile the issues HTML
		$issuesHtml = '';
		foreach ($this->issues as $issue)
		{
			$issuesHtml .= $issue['description_html'];
		}
		$html = $this->report['description_html'] . $issuesHtml;

		// Let's delete this item first (and any dups, which should not exist)
		$foundDocs = $this->index->find('pk:' . $this->report['id']);
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
		$doc = ZendSearch\Lucene\Document\HTML::loadHtml($html);

		// Add useful fields
		$doc->addField(\ZendSearch\Lucene\Document\Field::keyword('pk', $this->report['id']));
		$doc->addField(\ZendSearch\Lucene\Document\Field::text('title', $this->report['title']));

		// Add URLs
		foreach ($this->urls as $ord => $url)
		{
			$keyName = 'url' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $url['url']));
		}

		// Add issue keywords
		foreach ($this->issues as $ord => $issue)
		{
			$keyName = 'issue' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $issue['code']));
		}

		$this->getIndex()->addDocument($doc);
		// @todo Add logger
		$this->log("Added report " . $report['id'], 1);
	}

	/**
	 * Filter search before passing query to Zend Lucene
	 * 
	 * @todo Put in quoting device here for colon queries (for items
	 * that are not already quoted)
	 * 
	 * @param type $query
	 */
	public function search($query)
	{
		return $this->searchWithZend($query);
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
		echo $message . "\n";
	}
}