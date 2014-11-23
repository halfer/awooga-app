<?php

namespace Awooga\Testing;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class GitImporterTest extends TestCase
{
	/**
	 * Loads the classes we need
	 */
	public function setUp()
	{
		parent::setUp();

		$root = $this->getProjectRoot();
		require_once $root . '/src/classes/Exceptions/FileException.php';
		require_once $root . '/src/classes/GitImporter.php';
		// @todo Rename this to GitImporterTestHarness
		require_once $root . '/test/unit/classes/GitImporterHarness.php';
	}

	/**
	 * Checks that an ordinary clone works fine
	 */
	public function testCloneSuccess()
	{
		$relativePath = 'success';
		$importer = $this->getImporterInstance();
		$importer->setCheckoutPath($relativePath);

		// Do a fake clone
		$actualPath = $importer->cloneRepo($url = 'dummy');
		$this->assertEquals($relativePath, $actualPath, "Ensure an ordinary clone is OK");
	}

	/**
	 * Check that git failure in doClone causes an exception
	 * 
	 * @expectedException \Awooga\Exceptions\SeriousException
	 */
	public function testCloneGitFailure()
	{
		$relativePath = 'success';
		$importer = $this->getImporterInstance();
		$importer->setCheckoutPath($relativePath);

		// Get the fake clone to fail
		$importer->makeGitFail();
		$importer->cloneRepo($url = 'dummy');
	}
	
	/**
	 * Check a new repo has its location updated correctly
	 * 
	 * Strategy:
	 * 
	 * - Create a repo in data with a (default) null path
	 * - Point to a new location
	 * - Check the database now reads correctly
	 */
	public function testUpdateRepoSuccess()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();

		$relativePath = 'success';
		$importer = $this->getImporterInstance($pdo);
		$importer->setCheckoutPath($relativePath);

		// Let's check the mount path first, make sure it starts as null
		$sql = "SELECT mount_path FROM repository WHERE id = :repo_id";
		$mountPath = $this->fetchColumn($pdo, $sql, array(':repo_id' => $repoId, ));
		$this->assertNull($mountPath, "Check the mount path on a new repo is null");

		// Let's update the location
		$expectedPath = 'new-path';
		$importer->moveRepo($repoId, $oldPath = null, $expectedPath);
		$newMountPath = $this->fetchColumn($pdo, $sql, array(':repo_id' => $repoId, ));
		$this->assertEquals($expectedPath, $newMountPath, "Check repo path is reset");
	}

	/**
	 * Check an existing repo has its location updated and old repo removed
	 * 
	 * Strategy:
	 * 
	 * - Same as testUpdateRepoSuccess, but create a temp location that can be deleted
	 */
	public function testMoveRepoSuccess()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		$newRelativePath = 'success';
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($this->getDriver(), $tempRoot);

		// Let's create a folder we can delete
		$oldRelativePath = 'path' . rand(1, 9999999) . time();
		$oldPath = $tempRoot . '/' . $oldRelativePath;
		mkdir($oldPath);

		// Check that the folder exists here, to avoid permissions issues making it pass
		$this->assertTrue(file_exists($oldPath), "Check new folder actually exists");

		// Now let's do a "move", so that this folder is deleted
		$importer->moveRepo($repoId, $oldRelativePath, $newRelativePath);

		// Check that the folder is now gone
		$this->assertFalse(file_exists($oldPath), "Check the folder has now been zapped");		
	}

	/**
	 * Ensures the move method borks if it doesn't have a repo root
	 * 
	 * @expectedException \Awooga\Exceptions\SeriousException
	 */
	public function testMoveThrowsExceptionOnEmptyRootPath()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Temporary path to delete (it won't actually happen, so it does not need to exist)
		$oldPath = 'dummy1';
		$newPath = 'dummy2';

		$importer = $this->getImporterInstance($this->getDriver(), $repoRoot = '');
		$importer->moveRepo($repoId, $oldPath, $newPath);
	}

	/**
	 * Check file system failure for moveRepoLocation
	 * 
	 * @expectedException \Awooga\Exceptions\SeriousException
	 */
	public function testMoveRepoLocationFileSystemFailure()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		$newRelativePath = 'success';
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($this->getDriver(), $tempRoot);

		// Let's NOT create a folder, so a file system error is caused
		$oldRelativePath = $this->randomLeafname();

		// Now let's do a "move" set up to fail
		$importer->makeMoveFail();
		$importer->moveRepo($repoId, $oldRelativePath, $newRelativePath);		
	}

	/**
	 * Check scanRepo success
	 */
	public function testScanRepoSuccess()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Scan everything in this repo
		$importer = $this->getImporterInstance($this->getDriver());
		$importer->scanRepoWithLogging($repoId, $newRelativePath = 'success');

		// Check the numbers of scanned vs successful
		$this->checkFilesSeen($importer, 2);
		$this->checkReportsSuccessful($repoId, 2);
		$this->checkLogsGenerated(1, 0, 0);
	}

	/**
	 * Check that a trivial exception stops the scan of a report
	 */
	public function testScanRepoTrivialExceptionRaised()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Check we have no logs to start with
		$this->checkLogsGenerated(0, 0, 0);

		// Scan everything in this repo
		$importer = $this->getImporterInstance($this->getDriver());
		$importer->scanRepo($repoId, $newRelativePath = 'fail');

		// Check the numbers of scanned vs successful
		$this->checkFilesSeen($importer, 1);
		$this->checkReportsSuccessful($repoId, 0);
		$this->checkLogsGenerated(0, 0, 1);
	}

	/**
	 * Check that a number of trivial exceptions stops the scan of, and disables, the whole repo
	 */
	public function testRepoAfterExcessExceptionsRaised()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Check we have no logs to start with
		$this->checkLogsGenerated(0, 0, 0);

		// Set up a pretend repo
		$tempRoot = $this->getTempFolder();
		$pdo = $this->getDriver();
		$importer = $this->getImporterInstance($pdo, $tempRoot);

		// Create a repo and a few bad reports
		list($absolutePath, $relativePath) = $this->createTempRepoFolder();
		$maxFails = \Awooga\GitImporter::MAX_FAILS_BEFORE_DISABLE;
		for ($i = 0; $i < $maxFails * 2; $i++)
		{
			file_put_contents(
				$absolutePath . '/' . $i . '.json',
				'Bad report'
			);
		}

		// Try scanning the resulting mess, fail test if it does not raise exception
		$throwsException = false;
		try
		{
			$importer->scanRepo($repoId, $relativePath);
		}
		catch (\Awooga\Exceptions\SeriousException $e)
		{
			$throwsException = true;
		}
		$this->assertTrue($throwsException, "Make sure a serious exception is thrown");

		// Make sure the repo is now disabled
		$isEnabled = (boolean) $this->fetchColumn(
			$pdo,
			"SELECT is_enabled FROM repository WHERE id = :repo_id",
			array(':repo_id' => $repoId, )
		);
		$this->assertFalse($isEnabled, "Check the repository is now disabled");

		// Check that the number of files seen is less than the total
		$this->checkFilesSeen($importer, $maxFails + 1);

		// The extra row is the extra item to explain the repo has been disabled
		$this->checkLogsGenerated(0, 1, $maxFails + 1);

		// Delete the folder
		exec("rm -rf {$absolutePath}");
	}

	/**
	 * Ensure that an overly large report causes failure
	 */
	public function testFailOnMassiveJsonReport()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Check we have no logs to start with
		$this->checkLogsGenerated(0, 0, 0);

		// Set up a pretend repo
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($this->getDriver(), $tempRoot);

		// Create a repo and one huge report that should fail
		list($absolutePath, $relativePath) = $this->createTempRepoFolder();
		$report = array(
			'version' => 1,
			'title' => 'Demo title',
			'url' => 'http://example.com/very_large',
			'description' => str_repeat('abcdefghij', 6001),
			'issues' => array(
				'issue-cat-code' => 'sql-injection',
			),
		);
		file_put_contents(
			$absolutePath . '/big-report.json',
			json_encode($report)
		);

		// Do some scanning!
		$importer->scanRepo($repoId, $relativePath);

		// Check the numbers of scanned vs successful
		$this->checkFilesSeen($importer, 1);
		$this->checkReportsSuccessful($repoId, 0);
		$this->checkLogsGenerated(0, 0, 1);

		// Remove temporary repo
		$importer->deleteOldRepo($relativePath);
	}

	/**
	 * Checks that a serious exception rolls back changes in scanRepoWithLogging
	 * 
	 * @todo Write this
	 */
	public function testRollbackOnSeriousException()
	{
		
	}

	/**
	 * Check that a repo may not contain two reports that refer to the same URL
	 * 
	 * @todo Write this
	 */
	public function testFailOnDuplicateReportUrlsInRepo()
	{
		
	}

	/**
	 * Checks the rescheduler works
	 */
	public function testRescheduleRepo()
	{		
		$repoId = $this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();
		$importer = $this->getImporterInstance($pdo);

		// Check the due time is empty to start with
		$sql = "SELECT due_at FROM repository WHERE id = :repo_id";
		$this->assertNull(
			$this->fetchColumn($pdo, $sql, array(':repo_id' => $repoId, )),
			"Check the due time is empty"
		);

		$importer->rescheduleRepo($repoId, true);

		$this->assertNotNull(
			$this->fetchColumn($pdo, $sql, array(':repo_id' => $repoId, )),
			"Check the due time is not empty"
		);
	}

	/**
	 * Checks the logging system seems to be working
	 */
	public function testLogMessage()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Check we have no logs to start with
		$this->checkLogsGenerated(0, 0, 0);

		$importer = $this->getImporterInstance($this->getDriver());

		// These are the log levels we will test
		$logLevels = array(
			\Awooga\GitImporter::LOG_LEVEL_SUCCESS,
			\Awooga\GitImporter::LOG_LEVEL_ERROR_TRIVIAL,
			\Awooga\GitImporter::LOG_LEVEL_ERROR_SERIOUS
		);

		// Make success/trivial/serious logs of each type
		$makeLogs = array(
			\Awooga\GitImporter::LOG_TYPE_FETCH,
			\Awooga\GitImporter::LOG_TYPE_MOVE,
			\Awooga\GitImporter::LOG_TYPE_SCAN,
			\Awooga\GitImporter::LOG_TYPE_RESCHED,
		);
		foreach ($makeLogs as $logType)
		{
			$importer->repoLog($repoId, $logType);
			$importer->repoLog($repoId, $logType, null, $logLevels[1]);
			$importer->repoLog($repoId, $logType, null, $logLevels[2]);
		}

		// Check they generated OK
		foreach ($this->getLogs($repoId) as $ord => $actualLog)
		{
			$expectedType = $makeLogs[$ord / 3];
			$expectedLevel = $logLevels[$ord % 3];
			$this->assertEquals($expectedType, $actualLog['log_type']);
			$this->assertEquals($expectedLevel, $actualLog['log_level']);
		}
	}

	/**
	 * Try an end to end test
	 */
	public function testEndToEnd()
	{
		// Set up repository and importer
		$repoId = $this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();
		$importer = $this->getImporterInstance($pdo);

		// This (plus the harness) means we don't actually do a file move (hmm, should we?)
		$importer->setCheckoutPath($relativePath = 'success');

		$importer->processRepo($repoId, 'http://example.com', '/dummy/old/path');
		
		$this->assertEquals(
			2,
			$this->fetchColumn($pdo, "SELECT COUNT(*) FROM report"),
			"Check the number of reports is correct"
		);
		$this->assertEquals(
			2,
			$this->fetchColumn($pdo, "SELECT COUNT(*) FROM report_issue"),
			"Check the number of issues is correct"
		);
		$this->assertEquals(
			2,
			$this->fetchColumn($pdo, "SELECT COUNT(*) FROM resource_url"),
			"Check the number of URLs is correct"
		);
		$this->assertEquals(
			4,
			$this->fetchColumn($pdo, "SELECT COUNT(*) FROM repository_log"),
			"Check the number of logs is correct"
		);
	}

	/**
	 * Ensure that retry times increase on failure and reset on success
	 */
	public function testRepeatedFailsIncreasesRetryInterval()
	{
		// Build database
		$repoId = $this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();

		// Check increasing the fail count increases the retry time
		$oldMinutes = $this->runRepeatedFails(
			$repoId,
			1,
			10,
			function($test, $minutes, $oldMinutes) {
				$test->assertTrue(
					$minutes > $oldMinutes,
					"Ensure the wait time increases on each successive fail"
				);
			}
		);

		// Do a successful call
		$importer = $this->getImporterInstanceWithRun($pdo, 11);
		$importer->setCheckoutPath($relativePath = 'success');
		$importer->processRepo($repoId, 'http://example.com', '/dummy/old/path');

		// Now do another failed call, check the retry has been reset
		$this->runRepeatedFails(
			$repoId,
			12,
			1,
			function($test, $minutes, $oldMinutes) {
				$test->assertTrue(
					$minutes < $oldMinutes,
					"Ensure the wait time increases on each successive fail"
				);
			},
			$oldMinutes
		);
	}

	protected function runRepeatedFails($repoId, $startRunId, $count, callable $test, $oldMinutes = null)
	{
		$pdo = $this->getDriver();

		// Get the last date (may be null)
		$oldDate = \DateTime::createFromFormat(
			'Y-m-d H:i:s', $this->fetchColumn(
				$pdo,
				"SELECT due_at FROM repository WHERE id = :repo_id",
				array(':repo_id' => $repoId, )
			)
		);

		// Do this across a number of faked runs (the run rows don't actually exist)
		for($runId = $startRunId; $runId < $startRunId + $count; $runId++)
		{
			// Set up a repo for a serious failure
			$importer = $this->getImporterInstanceWithRun($pdo, $runId);
			$importer->setCheckoutPath($relativePath = 'success');
			$importer->makeGitFail();
			$importer->processRepo($repoId, 'http://example.com', '/dummy/old/path');

			// Check we have the right number of contiguous errors
			$this->assertEquals(1 + $runId - $startRunId, $importer->countRecentFails($repoId));

			// Check that the rescheduling increases with each failure
			$dueDate = $this->fetchColumn(
				$pdo,
				"SELECT due_at FROM repository WHERE id = :repo_id",
				array(':repo_id' => $repoId, )
			);

			// Parse the date and fail if it is not created
			$date = \DateTime::createFromFormat('Y-m-d H:i:s', $dueDate);
			if (!$date)
			{
				throw new \Exception('Could not parse next due date');
			}

			/* @var $date \DateTime */
			if ($oldDate)
			{
				$diff = $date->diff($oldDate);
				$minutes =
					$diff->d * 24 * 60 +
					$diff->h * 60 +
					$diff->i
				;
				// This calls the callback for the assertion
				$test($this, $minutes, $oldMinutes);
				$oldMinutes = $minutes;
			}
			$oldDate = $date;
		}

		return $oldMinutes;
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

	protected function checkFilesSeen(\Awooga\GitImporter $importer, $expectedCount)
	{
		// Check we've scanned the right number of reports
		$this->assertEquals(
			$expectedCount,
			$importer->getReportCount(),
			"Check we've processed the right number of reports"
		);
	}

	protected function checkReportsSuccessful($repoId, $expectedCount)
	{
		$sql = "SELECT COUNT(*) FROM report WHERE repository_id = :repo_id";
		$count = $this->fetchColumn($this->getDriver(), $sql, array('repo_id' => $repoId, ));

		$this->assertEquals(
			$expectedCount,
			$count,
			"Check we've saved the right number of successful reports"
		);
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

	protected function getLogs($repoId)
	{
		$sql = "
			SELECT *
			FROM repository_log
			WHERE repository_id = :repo_id
			ORDER BY id
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':repo_id' => $repoId, ));
		$logs = $statement->fetchAll(\PDO::FETCH_ASSOC);

		return $logs;
	}
}