<?php

namespace Awooga\Testing;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class UpdateAllTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();

		$root = $this->getProjectRoot();
		require_once $root . '/src/classes/UpdateAll.php';
		require_once $root . '/test/unit/classes/UpdateAllTestHarness.php';
		require_once $root . '/src/classes/GitImporter.php';
		require_once $root . '/test/unit/classes/GitImporterHarness.php';
	}

	/**
	 * Checks that a run can be created
	 */
	public function testCreateRun()
	{
		$this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();

		$updater = new UpdateAllTestHarness();
		$updater->setDriver($pdo);
		$runId = $updater->createRun();

		$this->assertNotNull(
			$runId,
			"Ensuring a run ID can be generated"
		);
	}

	/**
	 * Checks that the next method will grab the next due rows
	 */
	public function testNextRepos()
	{
		// Build the database, creates one default repo
		$this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();

		// Create 10 repos in the database
		$sql = "
			INSERT INTO repository
			(id, url, created_at)
			VALUES (:id, :url, NOW())
		";
		$statement = $pdo->prepare($sql);

		for($i = 2; $i <= 11; $i++)
		{
			$ok = $statement->execute(
				array(
					':id' => $i,
					':url' => 'http://example.com/repo_' . $i,
				)
			);
			if (!$ok)
			{
				throw new \Exception(
					'Database error: ' . print_r($statement->errorInfo(), true)
				);
			}
		}

		// Create updater and importer classes, create a run
		$updater = new UpdateAllTestHarness();
		$updater->setDriver($pdo);
		$importer = $this->getImporterInstanceWithRun($pdo, $updater->createRun());
		$updater->setImporter($importer);

		// Request some repos and make sure we get the right number
		$processSize = 7;
		$repos = $updater->getNextRepos($processSize);
		$this->assertEquals(
			$processSize,
			count($repos),
			"Ensure the first set of repos is the right size"
		);
		
		// Let's now add success logs against all of them
		foreach($repos as $repo)
		{
			$importer->repoLog($repo['id'], \Awooga\GitImporter::LOG_TYPE_FETCH);
		}

		// Request another 7, should get 4 more
		$reposNext = $updater->getNextRepos($processSize);
		$this->assertEquals(
			4,
			count($reposNext),
			"Ensure the next set of repos is the right size"
		);

		// Let's now add success logs against all of them
		foreach($reposNext as $repo)
		{
			$importer->repoLog($repo['id'], \Awooga\GitImporter::LOG_TYPE_FETCH);
		}

		// Ensure another request starts again
		$reposLast = $updater->getNextRepos($processSize);
		$this->assertEquals(
			$processSize,
			count($reposLast),
			"Ensure the next set of repos, from the start, is the right size"
		);
	}

	public function testUpdateSimple()
	{
		
	}

	public function testGetNext()
	{
		
	}
}