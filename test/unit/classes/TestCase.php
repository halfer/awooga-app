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
		require_once $root . '/src/autoload.php';
		require_once $root . '/test/unit/classes/RepoBuilder.php';
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
	protected function buildDatabase(\PDO $pdo, $createRepo = true)
	{
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/build/init.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/build/database/create.sql');

		// Create a repo if required
		$repoId = null;
		if ($createRepo)
		{
			$builder = new RepoBuilder();
			$builder->setDriver($pdo);
			$repoId = $builder->create(1);
		}

		return $repoId;
	}

	protected function createTempRepoFolder()
	{
		$relativePath = $this->randomLeafname();
		$absolutePath = $this->getTempFolder() . '/' . $relativePath;
		mkdir($absolutePath);

		return array($absolutePath, $relativePath);
	}

	protected function randomLeafname()
	{
		return 'path' . rand(1, 9999999) . time();
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
	 * @return \Awooga\Testing\GitImporterTestHarness
	 */
	protected function getImporterInstance(\PDO $pdo = null, $repoRoot = null)
	{
		$importer = new GitImporterTestHarness(
			is_null($repoRoot) ? $this->getTestRepoRoot() : $repoRoot
		);
		$importer->setRunId(1);
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
	 * @param string $repoRoot
	 * @return \Awooga\Testing\GitImporterHarness
	 */
	protected function getImporterInstanceWithRun(\PDO $pdo, $runId, $repoRoot = null)
	{
		$importer = new GitImporterTestHarness(
			is_null($repoRoot) ? $this->getTestRepoRoot() : $repoRoot
		);
		$importer->setDriver($pdo);
		$importer->setRunId($runId);

		return $importer;
	}

	/**
	 * Checks the number of logs at different levels
	 * 
	 * @param integer $expectedSuccess
	 * @param integer $expectedSerious
	 * @param integer $expectedTrivial
	 */
	protected function checkLogsGenerated($expectedSuccess, $expectedSerious, $expectedTrivial)
	{
		$sql = "
			SELECT
				(SELECT COUNT(*) FROM repository_log WHERE log_level = 'success') success_count,
				(SELECT COUNT(*) FROM repository_log WHERE log_level = 'serious') serious_count,
				(SELECT COUNT(*) FROM repository_log WHERE log_level = 'trivial') trivial_count
			FROM dual
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();
		$counts = $statement->fetch(\PDO::FETCH_ASSOC);

		$this->assertEquals(
			$expectedSuccess,
			$counts['success_count'],
			"Check the number of success logs is correct"
		);
		$this->assertEquals(
			$expectedSerious,
			$counts['serious_count'],
			"Check the number of fail logs is correct"
		);
		$this->assertEquals(
			$expectedTrivial,
			$counts['trivial_count'],
			"Check the number of fail logs is correct"
		);
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