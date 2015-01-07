<?php

namespace Awooga\Testing\Browser;

/**
 * Let's have some declarations for magic methods
 *
 * @todo Can we move these upstream to PHPUnitSpiderling\Testcase_Spiderling?
 * 
 * @method \Openbuildings\Spiderling\Page visit($uri, array $query = array()) Initiate a visit with the currently selected driver
 * @method string content() Return the content of the last request from the currently selected driver
 * @method string current_path() Return the current browser url without the domain
 * @method string current_url() Return the current url
 * @method \Openbuildings\Spiderling\Functest_Node assertHasCss($selector, array $filters = array(), $message = NULL)
 */
abstract class TestCase extends \Openbuildings\PHPUnitSpiderling\Testcase_Spiderling
{
	use \Awooga\Testing\BaseTestCase;

	const DOMAIN = 'http://localhost:8090';

	// Change this to turn logging back on
	const LOG_ACTIONS = false;

	/**
	 * Common library loading for all test classes
	 */
	public function setUp()
	{
		$this->buildDatabase($this->getDriver(false));
		$this->indexDocuments();
	}

	/**
	 * Let's set add some logging here, to see why PhantomJS is flaky on Travis
	 */
	public function driver_phantomjs()
	{
		// We can supply a log location here (or omit to use /dev/null)
		$logFile = '/tmp/phantom-awooga.log';
		$connection = new \Openbuildings\Spiderling\Driver_Phantomjs_Connection();
		$connection->start(null, self::LOG_ACTIONS ? $logFile : '/dev/null');

		$driver = new \Openbuildings\Spiderling\Driver_Phantomjs();
		$driver->connection($connection);

		return $driver;
	}

	/**
	 * Creates the test database
	 * 
	 * @param \PDO $pdo
	 */
	protected function buildDatabase(\PDO $pdo)
	{
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/build/init.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/build/database/create.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/browser/fixtures/data.sql');
	}

	/**
	 * Adds the fixtures reports to a test search index
	 */
	protected function indexDocuments()
	{
		// For now, if the index exists, let us not recreate it
		$indexPath = $this->getProjectRoot() . '/filesystem/tmp/search-index';
		if (file_exists($indexPath))
		{
			return;
		}

		$pdo = $this->getDriver();
		$statement = $pdo->prepare(
			'SELECT
				id, title, description_html
			FROM report WHERE is_enabled = 1'
		);
		$statement->execute();

		$searcher = new \Awooga\Core\Searcher();
		$searcher->connect($indexPath);

		while ($report = $statement->fetch(\PDO::FETCH_ASSOC))
		{
			$searcher->index(
				$report,
				$this->getUrls($pdo, $report['id']),
				$this->getIssues($pdo, $report['id'])
			);
		}
	}

	/**
	 * Returns the URLs related to the specified report in an array
	 * 
	 * @param \PDO $pdo
	 * @param integer $reportId
	 * @return array
	 */
	protected function getUrls(\PDO $pdo, $reportId)
	{
		$statement = $pdo->prepare(
			'SELECT
				url
			FROM
				resource_url
			WHERE
				report_id = :report_id'
		);

		$statement->execute(array(':report_id' => $reportId, ));
		$urls = array();
		while ($url = $statement->fetchColumn())
		{
			$urls[] = $url;
		}

		return $urls;
	}

	/**
	 * Returns the issues related to the specified report in an array
	 * 
	 * @param \PDO $pdo
	 * @param integer $reportId
	 * @return array
	 */
	protected function getIssues(\PDO $pdo, $reportId)
	{
		$statement = $pdo->prepare(
			'SELECT
				issue.code issue_cat_code,
				ri.description_html
			FROM report_issue ri
			INNER JOIN issue ON (ri.issue_id = issue.id)
			WHERE
				ri.report_id = :report_id'
		);
		$statement->execute(array(':report_id' => $reportId, ));

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Logs in the test user
	 */
	protected function loginTestUser()
	{
		// Logon and then check that it worked
		$this->visit(self::DOMAIN . '/auth?provider=test');
		$this->assertEquals('Logout testuser', $this->find('#auth-status')->text());
	}
}