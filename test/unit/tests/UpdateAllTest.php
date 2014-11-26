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
		require_once $root . '/test/unit/classes/GitImporterTestHarness.php';
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
		$this->buildDatabase($this->getDriver(false), false);
		$pdo = $this->getDriver();

		// Create 10 repos in the database
		$sql = "
			INSERT INTO repository
			(id, url, created_at)
			VALUES (:id, :url, NOW())
		";
		$statement = $pdo->prepare($sql);

		// Remember this table isn't auto-increment for the time being
		for($i = 1; $i <= 10; $i++)
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
		$runId = $updater->createRun();
		$importer = $this->getImporterInstanceWithRun($pdo, $runId);
		$updater->setImporter($importer);

		// Request some repos and make sure we get the right number
		$processSize = 7;
		list($firstRunId, $repos) = $updater->getNextRepos($processSize);
		$this->assertEquals(
			$processSize,
			count($repos),
			"Ensure the first set of repos is the right size"
		);

		// Check this is not on a run (since nothing has been logged against it yet)
		$this->assertNull($firstRunId, "Checking first run ID is null");
		
		// Let's now add success logs against all of them
		foreach($repos as $repo)
		{
			$importer->repoLog($repo['id'], \Awooga\GitImporter::LOG_TYPE_FETCH);
		}

		// Request another 7, should get 3 more
		list($nextRunId, $reposNext) = $updater->getNextRepos($processSize);
		$this->assertEquals(
			3,
			count($reposNext),
			"Ensure the next set of repos is the right size"
		);

		// Check this is on a run
		$this->assertEquals($runId, $nextRunId, "Checking returned run ID");

		// Let's now add success logs against all of them
		foreach($reposNext as $repo)
		{
			$importer->repoLog($repo['id'], \Awooga\GitImporter::LOG_TYPE_FETCH);
		}

		// Ensure another request starts again
		list($lastRunId, $reposLast) = $updater->getNextRepos($processSize);
		$this->assertEquals(
			$processSize,
			count($reposLast),
			"Ensure the next set of repos, from the start, is the right size"
		);

		// Check this is not on a run
		$this->assertNull($lastRunId, "Checking new set of repos has no run ID");
	}

	/**
	 * Does a simple end-to-end for a set of repositories
	 */
	public function testUpdateSimple()
	{
		// Build the database, creates one default repo
		$this->buildDatabase($this->getDriver(false), false);
		$pdo = $this->getDriver();

		$updater = new UpdateAllTestHarness();
		$updater->setDriver($pdo);
		$runId = $updater->createRun();
		$importer = $this->getImporterInstanceWithRun($pdo, $runId, $this->getTempFolder());
		$updater->setImporter($importer);

		// Create some repos
		for($repoId = 1; $repoId < 10; $repoId++)
		{
			// Write a new repo row
			$this->buildRepo($pdo, $repoId);
		}

		// Run a scan
		$updater->run(20, false);

		// @todo Let's examine some logs here to see if it worked
	}
}
