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
		require_once $root . '/src/classes/GitImporter.php';
		require_once $root . '/src/classes/Exceptions/FileException.php';
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
	 * Returns a new importer instance pointing to the current test repo
	 * 
	 * @param \PDO $pdo Database connection
	 * @param string $repoRoot Fully-qualified path to repository (optional)
	 * @return \Awooga\Testing\GitImporterHarness
	 */
	protected function getImporterInstance(\PDO $pdo = null, $repoRoot = null)
	{
		return new GitImporterHarness(
			is_null($repoRoot) ? $this->getTestRepoRoot() : $repoRoot,
			$pdo
		);
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
		$this->checkLogsGenerated($repoId, 1, 0);
	}

	/**
	 * Check that a trivial exception stops the scan of a report
	 */
	public function testScanRepoTrivialExceptionRaised()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Check we have no logs to start with
		$this->checkLogsGenerated($repoId, 0, 0);

		// Scan everything in this repo
		$importer = $this->getImporterInstance($this->getDriver());
		$importer->scanRepo($repoId, $newRelativePath = 'fail');

		// Check the numbers of scanned vs successful
		$this->checkFilesSeen($importer, 1);
		$this->checkReportsSuccessful($repoId, 0);
		$this->checkLogsGenerated($repoId, 0, 1);
	}

	/**
	 * Check that a number of trivial exceptions stops the scan of, and disables, the whole repo
	 */
	public function testRepoAfterExcessExceptionsRaised()
	{
		$repoId = $this->buildDatabase($this->getDriver(false));

		// Check we have no logs to start with
		$this->checkLogsGenerated($repoId, 0, 0);

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
		$this->checkLogsGenerated($repoId, 0, $maxFails + 2);

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
		$this->checkLogsGenerated($repoId, 0, 0);

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
		$this->checkLogsGenerated($repoId, 0, 1);
	}

	/**
	 * Checks that a serious exception rolls back changes in scanRepoWithLogging
	 */
	public function testRollbackOnSeriousException()
	{
		
	}

	/**
	 * Check that a repo may not contain two reports that refer to the same URL
	 */
	public function testFailOnDuplicateReportUrlsInRepo()
	{
		
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
	 * Checks the number of success/fail logs
	 * 
	 * @param integer $repoId
	 * @param integer $expectedSuccess
	 * @param integer $expectedFail
	 */
	protected function checkLogsGenerated($repoId, $expectedSuccess, $expectedFail)
	{
		$sql = "
			SELECT
				(SELECT COUNT(*) FROM repository_log WHERE is_success = 1) success_count,
				(SELECT COUNT(*) FROM repository_log WHERE is_success = 0) fail_count
			FROM dual
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':repo_id' => $repoId, ));
		$counts = $statement->fetch(\PDO::FETCH_ASSOC);

		$this->assertEquals(
			$expectedSuccess,
			$counts['success_count'],
			"Check the number of success logs is correct"
		);
		$this->assertEquals(
			$expectedFail,
			$counts['fail_count'],
			"Check the number of fail logs is correct"
		);
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