<?php

namespace Awooga\Testing\Unit;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	use \Awooga\Testing\BaseTestCase;
	use \Awooga\Traits\Runner;

	/**
	 * Creates the test database
	 * 
	 * @param \PDO $pdo
	 * @param boolean $createRepo
	 * @param boolean $createUser
	 * @return integer Repository ID
	 */
	protected function buildDatabase(\PDO $pdo, $createRepo = true, $createUser = false)
	{
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/build/init.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/build/database/create.sql');

		// Set up the entity builder in case we need it
		$builder = new RepoBuilder();
		$builder->setDriver($pdo);
		$id = null;

		// Create a repo if required
		if ($createRepo)
		{
			$id = $builder->create(1);
		}
		// Create a user if required
		elseif ($createUser)
		{
			$id = $builder->createUser();
		}

		return $id;
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

	/**
	 * Returns a new importer instance pointing to the current test repo
	 * 
	 * @param \PDO $pdo Database connection
	 * @param string $repoRoot Fully-qualified path to repository (optional)
	 * @param integer $runId
	 * @return GitImporterTestHarness
	 */
	protected function getImporterInstance(\PDO $pdo = null, $repoRoot = null, $runId = 1)
	{
		$importer = new GitImporterTestHarness(
			is_null($repoRoot) ? $this->getTestRepoRoot() : $repoRoot
		);
		$importer->setRunId($runId);
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
		return $this->getImporterInstance($pdo, $repoRoot, $runId);
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