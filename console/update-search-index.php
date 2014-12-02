<?php

/* 
 * Updates the search index
 */

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

// @todo Move this to a mounted FS, so we can size-limit
$searchIndex = $root . '/filesystem/index';

// Connect to the database
// @todo Pull this from env config
$dsn = 'mysql:dbname=awooga;host=localhost;username=awooga_user;password=password';
$pdo = new PDO($dsn, 'awooga_user', 'password');

class UpdateIndex
{
	use \Awooga\Core\Database;
	use \Awooga\Traits\Reports;

	public function __construct($searchIndex)
	{
		// Create index
		if (file_exists($searchIndex))
		{
			$this->index = ZendSearch\Lucene\Lucene::open($searchIndex);
		}
		else
		{
			$this->index = ZendSearch\Lucene\Lucene::create($searchIndex);
		}
	}

	public function scan()
	{
		$sql = "SELECT * FROM report WHERE is_enabled = 1";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		$reports = $statement->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($reports as $report)
		{
			$this->scanOne($report);
		}

		echo "Docs in index: " . $this->index->numDocs() . "\n\n";
	}

	protected function scanOne($report)
	{
		// Compile the issues HTML
		$issues = $this->getRelatedIssues(array($report['id']));
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
			echo "Warning, deleting more than one doc\n";
		}
		foreach ($foundDocs as $foundDoc)
		{
			$this->index->delete($foundDoc->id);
		}

		// Add in HTML
		$doc = ZendSearch\Lucene\Document\HTML::loadHtml($html);

		// Add useful fields
		$doc->addField(\ZendSearch\Lucene\Document\Field::keyword('pk', $report['id']));
		$doc->addField(\ZendSearch\Lucene\Document\Field::text('title', $report['title']));

		// Add URLs
		// @todo Can we add multiple urls with the same keyword name?
		$urls = $this->getRelatedUrls(array($report['id']));
		foreach ($urls as $ord => $url)
		{
			$keyName = 'url' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $url['url']));
			echo "  Add url: " . $url['url'] . "\n";
		}

		// Add issue keywords
		// @todo Can we add multiple issue codes with the same keyword name?
		foreach ($issues as $ord => $issue)
		{
			$keyName = 'issue' . $ord;
			$doc->addField(\ZendSearch\Lucene\Document\Field::keyword($keyName, $issue['code']));
			echo "  Add issue: " . $issue['code'] . "\n";
		}

		$this->index->addDocument($doc);
		echo "Added report " . $report['id'] . "\n";
	}

	public function doQuery($query)
	{
		// Save memory and make it snappy
		\ZendSearch\Lucene\Lucene::setResultSetLimit(200);

		echo "Search: $query\n";
		$foundDocs = $this->index->find($query);
		foreach ($foundDocs as $foundDoc)
		{
			echo "Found doc " . $foundDoc->pk . "\n";
		}
		echo "-----\n";
	}
}

$index = new UpdateIndex($searchIndex);
$index->setDriver($pdo);
$index->scan();

// Searches
$index->doQuery("YouTube"); // In one title
$index->doQuery("MySQL"); // In many titles
$index->doQuery('parameterisation'); // An issue
$index->doQuery('sql-injection'); // In many codes

// Let's try a specific code
$index->doQuery('issue0:sql-injection');
$index->doQuery('issue1:sql-needs-parameterisation');

// Look up URLs by field
$index->doQuery('url0:"http://vimeo.com/108934852"');
$index->doQuery('url1:"http://www.onlinetuting.com/create-login-script-in-php/"');

// Look up URL without field (can we turn off field searching, so the quotes are not necessary?)
$index->doQuery('"http://vimeo.com/108934852"');

// Not working atm
$index->doQuery("Amitpatel");

echo (memory_get_peak_usage(true) / 1024) . "K\n";
