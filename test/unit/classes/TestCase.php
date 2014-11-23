<?php

namespace Awooga\Testing;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Common library loading for all test classes
	 */
	public function setUp()
	{
		$root = $this->getProjectRoot();
		require_once $root . '/src/classes/Database.php';
		require_once $root . '/src/classes/Report.php';
		require_once $root . '/src/classes/Exceptions/SeriousException.php';
		require_once $root . '/src/classes/Exceptions/TrivialException.php';		
	}

	/**
	 * Gets a PDO driver
	 * 
	 * @todo Pull this from env config
	 * 
	 * @return \PDO
	 */
	protected function getDriver($selectDatabase = true)
	{
		// Connect to the database
		$database = $selectDatabase ? 'dbname=awooga_test;' : '';
		$dsn = "mysql:{$database}host=localhost;username=awooga_user_test;password=password";
		$pdo = new \PDO($dsn, 'awooga_user_test', 'password');

		return $pdo;
	}

	/**
	 * Creates the test database
	 * 
	 * @param \PDO $pdo
	 * @return integer Repository ID
	 */
	protected function buildDatabase(\PDO $pdo)
	{
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/build/init.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/build/create.sql');
		$repoId = $this->buildRepo($pdo, 1);

		return $repoId;
	}

	/**
	 * Creates a dummy repo account (hardwired ID for now)
	 * 
	 * @param \PDO $pdo
	 */
	protected function buildRepo(\PDO $pdo)
	{
		$repoId = 1;
		$sql = "
			INSERT INTO
				repository
			(id, url, created_at)
			VALUES ($repoId, 'http://example.com/repo.git', '2014-11-18')
		";
		$pdo->exec($sql);

		return $repoId;
	}

	protected function runSqlFile(\PDO $pdo, $sqlPath)
	{
		$sql = file_get_contents($sqlPath);

		return $this->runSql($pdo, $sql);
	}

	protected function runSql(\PDO $pdo, $sql)
	{
		$rows = $pdo->exec($sql);

		if ($rows === false)
		{
			print_r($pdo->errorInfo());
			throw new \Exception(
				"Could not initialise the database"
			);
		}
	}

	public function fetchResults(\PDO $pdo, $sql, array $params = array())
	{
		return $this->runStatement($pdo, $sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function fetchColumn(\PDO $pdo, $sql, array $params = array())
	{
		return $this->runStatement($pdo, $sql, $params)->fetchColumn();		
	}

	protected function runStatement(\PDO $pdo, $sql, array $params)
	{
		$statement = $ok = $pdo->prepare($sql);
		$ok = $statement->execute($params);

		return $statement;
	}

	/**
	 * Returns a new importer instance pointing to the current test repo
	 * 
	 * @param \PDO $pdo Database connection
	 * @param string $repoRoot Fully-qualified path to repository (optional)
	 * @return \Awooga\Testing\GitImporterHarness
	 */
	protected function getImporterInstance(\PDO $pdo = null, $repoRoot = null)
	{
		$importer = new GitImporterHarness(
			1,
			is_null($repoRoot) ? $this->getTestRepoRoot() : $repoRoot
		);
		if ($pdo)
		{
			$importer->setDriver($pdo);
		}

		return $importer;
	}

	/**
	 * Returns a new importer instance with a specific run ID
	 * 
	 * @param \PDO $pdo
	 * @param integer $runId
	 */
	protected function getImporterInstanceWithRun(\PDO $pdo, $runId)
	{
		$importer = new GitImporterHarness($runId, $this->getTestRepoRoot());
		$importer->setDriver($pdo);

		return $importer;
	}

	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../../..');
	}

	protected function getTestRepoRoot()
	{
		return $this->getProjectRoot() . '/test/unit/repos';
	}

	protected function getTempFolder()
	{
		return $this->getProjectRoot() . '/test/unit/tmp';		
	}
}