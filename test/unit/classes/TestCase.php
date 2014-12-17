<?php

namespace Awooga\Testing\Unit;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/../..') . '/traits/BaseTestCase.php';

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	use \Awooga\Testing\BaseTestCase;

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
	 * Creates the test database
	 * 
	 * @param \PDO $pdo
	 * @param boolean $createRepo
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

	/**
	 * Creates a temporary repository folder
	 * 
	 * @return array Duple containing absolute and relative paths
	 */
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

	public function fetchResults(\PDO $pdo, $sql, array $params = array())
	{
		return $this->runStatement($pdo, $sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Fetches a single column for the given SQL statement
	 * 
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function fetchColumn(\PDO $pdo, $sql, array $params = array())
	{
		return $this->runStatement($pdo, $sql, $params)->fetchColumn();		
	}

	/**
	 * Runs a parameterised piece of SQL and returns the statement object in which it was run
	 * 
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $params
	 * @return \PDOStatement
	 */
	protected function runStatement(\PDO $pdo, $sql, array $params)
	{
		$statement = $pdo->prepare($sql);
		$statement->execute($params);

		return $statement;
	}

	/**
	 * Returns a new importer instance pointing to the current test repo
	 * 
	 * @param \PDO $pdo Database connection
	 * @param string $repoRoot Fully-qualified path to repository (optional)
	 * @return GitImporterTestHarness
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
	 * @return GitImporterTestHarness
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

	/**
	 * Gets the fully-qualified path of the test repository folder
	 * 
	 * @return string
	 */
	protected function getTestRepoRoot()
	{
		return $this->getProjectRoot() . '/test/unit/repos';
	}

	/**
	 * Gets the fully-qualified path of the temporary folder
	 * 
	 * @return string
	 */
	protected function getTempFolder()
	{
		return $this->getProjectRoot() . '/test/unit/tmp';		
	}
}