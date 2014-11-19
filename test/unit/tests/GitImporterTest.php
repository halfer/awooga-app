<?php

namespace Awooga\Testing;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class GitImporterTest extends TestCase
{
	/**
	 * Set up database
	 */
	public function setUp()
	{
		$root = $this->getProjectRoot();

		require_once $root . '/src/classes/GitImporter.php';
		require_once $root . '/src/classes/Exceptions/SeriousException.php';
		// @todo Rename this to GitImporterTestHarness
		require_once $root . '/test/unit/classes/GitImporterHarness.php';
	}

	/**
	 * Checks that an ordinary clone works fine
	 */
	public function testCloneSuccess()
	{
		$relativePath = 'success';
		$importer = $this->getImporterInstance($relativePath);
		$importer->setCheckoutPath($relativePath);

		// Do a fake clone
		$actualPath = $importer->doClone($url = 'dummy');
		$this->assertEquals($relativePath, $actualPath, "Ensure an ordinary clone is OK");
	}

	/**
	 * Returns a new importer instance pointing to the current test repo
	 * 
	 * @param string $repoPath Relative path to repository
	 * @param string $repoRoot Fully-qualified path to repository (optional)
	 * @return \Awooga\Testing\GitImporterHarness
	 */
	protected function getImporterInstance($repoPath, $repoRoot = null)
	{
		return new GitImporterHarness(
			$this->getDriver(),
			is_null($repoRoot) ? $this->getTestRepoRoot($repoPath) : $repoRoot
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
		$importer = $this->getImporterInstance($relativePath);
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
		$importer = $this->getImporterInstance($relativePath);
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
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);

		$newRelativePath = 'success';
		$tempRoot = $this->getTempFolder();
		$importer = $this->getImporterInstance($newRelativePath, $tempRoot);

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
		$pdo = $this->getDriver();
		$repoId = $this->buildDatabase($pdo);

		// Temporary path to delete (it won't actually happen, so it does not need to exist)
		$oldPath = 'dummy1';
		$newPath = 'dummy2';

		$importer = $this->getImporterInstance('success', $repoRoot = '');
		$importer->moveRepoLocation($repoId, $oldPath, $newPath);
	}

	public function testMoveThrowsExceptionOnMissingOldPath()
	{
		
	}

	/**
	 * Check perm failure for moveRepoLocation
	 */
	public function testMoveRepoLocationFileSystemFailure()
	{
		
	}

	/**
	 * Check scanRepo success
	 */
	public function testScanRepoSuccess()
	{
		
	}

	/**
	 * Check that a trivial exception stops the scan of a report
	 */
	public function testScanRepoTrivialExceptionRaised()
	{
		
	}

	/**
	 * Check that a number of trivial exceptions stops the scan of the whole repo
	 */
	public function testScanRepoBombOutAfterExcessExceptionsRaised()
	{
		
	}

	/**
	 * Ensure that a report that cannot be decoded is handled correctly
	 */
	public function testFailOnBadJsonReport()
	{
		
	}

	/**
	 * Ensure that an overly large report causes failure
	 */
	public function testFailOnMassiveJsonReport()
	{
		
	}

	/**
	 * Checks the repo logger works
	 */
	public function testRepoLog()
	{
		
	}

	protected function getTestRepoRoot($repoName)
	{
		return $this->getProjectRoot() . '/test/unit/repos/' . $repoName;
	}

	protected function getTempFolder()
	{
		return $this->getProjectRoot() . '/test/unit/tmp';		
	}
}