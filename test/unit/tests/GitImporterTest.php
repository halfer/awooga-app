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
		$actualPath = $importer->doClone($url = 'dummy');
		$this->assertEquals($relativePath, $actualPath, "Ensure an ordinary clone is OK");
	}

	/**
	 * Returns a new importer instance pointing to the current test repo
	 * 
	 * @param string $repoRoot Fully-qualified path to repository (optional)
	 * @return \Awooga\Testing\GitImporterHarness
	 */
	protected function getImporterInstance($repoRoot = null)
	{
		return new GitImporterHarness(
			$this->getDriver(),
			is_null($repoRoot) ? $this->getTestRepoRoot() : $repoRoot
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
		$importer->doClone($url = 'dummy');
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
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);

		$relativePath = 'success';
		$importer = $this->getImporterInstance();
		$importer->setCheckoutPath($relativePath);

		// Let's check the mount path first, make sure it starts as null
		$sql = "SELECT mount_path FROM repository WHERE id = :repo_id";
		$mountPath = $this->fetchColumn($pdo, $sql, array(':repo_id' => $repoId, ));
		$this->assertNull($mountPath, "Check the mount path on a new repo is null");

		// Let's update the location
		$expectedPath = 'new-path';
		$importer->moveRepoLocation($repoId, $oldPath = null, $expectedPath);
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
		$repoId = $this->buildDatabase($this->getDriver());

		$newRelativePath = 'success';
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($tempRoot);

		// Let's create a folder we can delete
		$oldRelativePath = 'path' . rand(1, 9999999) . time();
		$oldPath = $tempRoot . '/' . $oldRelativePath;
		mkdir($oldPath);

		// Check that the folder exists here, to avoid permissions issues making it pass
		$this->assertTrue(file_exists($oldPath), "Check new folder actually exists");

		// Now let's do a "move", so that this folder is deleted
		$importer->moveRepoLocation($repoId, $oldRelativePath, $newRelativePath);

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
		$repoId = $this->buildDatabase($this->getDriver());

		// Temporary path to delete (it won't actually happen, so it does not need to exist)
		$oldPath = 'dummy1';
		$newPath = 'dummy2';

		$importer = $this->getImporterInstance($repoRoot = '');
		$importer->moveRepoLocation($repoId, $oldPath, $newPath);
	}

	/**
	 * Check file system failure for moveRepoLocation
	 * 
	 * @expectedException \Awooga\Exceptions\SeriousException
	 */
	public function testMoveRepoLocationFileSystemFailure()
	{
		$repoId = $this->buildDatabase($this->getDriver());

		$newRelativePath = 'success';
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($tempRoot);

		// Let's NOT create a folder, so a file system error is caused
		$oldRelativePath = $this->randomLeafname();

		// Now let's do a "move" set up to fail
		$importer->makeMoveFail();
		$importer->moveRepoLocation($repoId, $oldRelativePath, $newRelativePath);		
	}

	/**
	 * Check scanRepo success
	 */
	public function testScanRepoSuccess()
	{
		$repoId = $this->buildDatabase($this->getDriver());

		// Scan everything in this repo
		$importer = $this->getImporterInstance();
		$importer->scanRepo($repoId, $newRelativePath = 'success');

		// Check the numbers of scanned vs successful
		$this->checkFilesSeen($importer, 2);
		$this->checkReportsSuccessful($repoId, 2);
	}

	/**
	 * Check that a trivial exception stops the scan of a report
	 */
	public function testScanRepoTrivialExceptionRaised()
	{
		$repoId = $this->buildDatabase($this->getDriver());

		// Scan everything in this repo
		$importer = $this->getImporterInstance();
		$importer->scanRepo($repoId, $newRelativePath = 'fail');

		// Check the numbers of scanned vs successful
		$this->checkFilesSeen($importer, 1);
		$this->checkReportsSuccessful($repoId, 0);
	}

	/**
	 * Check that a number of trivial exceptions stops the scan of, and disables, the whole repo
	 */
	public function testRepoAfterExcessExceptionsRaised()
	{
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);

		// Set up a pretend repo
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($tempRoot);

		// Create a repo...
		$relativePath = $this->randomLeafname();
		$absolutePath = $tempRoot . '/' . $relativePath;
		mkdir($absolutePath);

		// ... and a few bad reports
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

		// Delete the folder
		exec("rm -rf {$absolutePath}");
	}

	/**
	 * Ensure that an overly large report causes failure
	 */
	public function testFailOnMassiveJsonReport()
	{
		
	}

	/**
	 * Check that a repo may not contain two reports that refer to the same URL
	 */
	public function testFailOnDuplicateReportUrlsInRepo()
	{
		
	}

	/**
	 * Checks the repo logger works
	 */
	public function testRepoLog()
	{
		
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

	protected function getTestRepoRoot()
	{
		return $this->getProjectRoot() . '/test/unit/repos';
	}

	protected function getTempFolder()
	{
		return $this->getProjectRoot() . '/test/unit/tmp';		
	}
}