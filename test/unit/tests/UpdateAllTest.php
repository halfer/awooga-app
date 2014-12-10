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
		require_once $root . '/test/unit/classes/UpdateAllTestHarness.php';
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
	 * 
	 * @todo Use RepoBuilder to create the repo, or maybe setupReposToProcess?
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
			$importer->repoLog($repo['id'], \Awooga\Core\GitImporter::LOG_TYPE_FETCH);
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
			$importer->repoLog($repo['id'], \Awooga\Core\GitImporter::LOG_TYPE_FETCH);
		}

		// Ensure another request starts again, since we've not actually processed them
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
	 * Checks that disabled repos are not processed
	 */
	public function testIgnoreDisabledRepo()
	{
		// Create repos, and then disable half of them
		$updater = $this->setupReposToProcess();
		$statement = $this->getDriver()->prepare(
			"UPDATE repository SET is_enabled = 0 WHERE id <= 5"
		);
		$statement->execute();

		// Ensure only half of the repos are returned as due
		list(, $repoRows) = $updater->getNextRepos(10);
		$this->assertEquals(
			5,
			count($repoRows),
			"Ensure disabled repos are not processed"
		);

		$this->cleanupTemporaryRepos();
	}

	/**
	 * Does a simple end-to-end for a set of repositories
	 */
	public function testUpdateSimple()
	{
		// Run a scan
		$updater = $this->setupReposToProcess();
		$updater->run(20, false);

		// Let's examine some logs here to see if it worked
		$this->checkLogsGenerated(40, 0, 0);

		$this->cleanupTemporaryRepos();
	}

	/**
	 * Check that a repo is not reprocessed when it isn't due
	 * 
	 * @todo Do other tests need setFailureExceptions, or do we need to reset the default in the harness?
	 * 
	 * @throws \Exception
	 */
	public function testRunUpdateOnlyWhenDue()
	{
		// Run a scan
		$updater = $this->setupReposToProcess();
		$updater->run(20, false);

		// ... then check that no repos are not due straight away
		list(, $repoRows1) = $updater->getNextRepos($repoCount = 10);
		$this->assertEmpty(
			$repoRows1,
			"Ensure repos that have just been processed are not due immediately"
		);

		$updater->setTimeOffset(new \DateInterval('PT5H'));

		// Ensure that a while later, all repos are now due
		list(, $repoRows2) = $updater->getNextRepos($repoCount);
		$this->assertEquals(
			$repoCount,
			count($repoRows2),
			"Ensure repos become due later"
		);

		$this->cleanupTemporaryRepos();
	}

	/**
	 * Sets up a number of copies of the same repo, ready to kick off the updater
	 * 
	 * @return \Awooga\Testing\UpdateAllTestHarness
	 */
	protected function setupReposToProcess()
	{
		// Build the database, creates one default repo
		$this->buildDatabase($this->getDriver(false), false);
		$pdo = $this->getDriver();

		$updater = new UpdateAllTestHarness();
		$updater->setDriver($pdo);
		$runId = $updater->createRun();
		$importer = $this->getImporterInstanceWithRun($pdo, $runId, $this->getTempFolder());
		$importer->setFailureExceptions(true);
		$updater->setImporter($importer);

		// Create some repos
		$builder = new RepoBuilder();
		$builder->setDriver($pdo);
		for($repoId = 1; $repoId <= 10; $repoId++)
		{
			// Write a new repo row (using URL as checkout source)
			$builder->create($repoId, $this->getTestRepoRoot() . '/success');
		}

		return $updater;
	}

	/**
	 * Deletes repositories that have just been created
	 */
	protected function cleanupTemporaryRepos()
	{
		// @todo Needs implementing
	}
}
