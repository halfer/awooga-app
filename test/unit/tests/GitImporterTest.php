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
	 * @param string $repoPath
	 * @return \Awooga\Testing\GitImporterHarness
	 */
	protected function getImporterInstance($repoPath)
	{
		return new GitImporterHarness(
			$this->getDriver(),
			$this->getTestRepoRoot($repoPath)
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
	 * Check success for moveRepoLocation
	 */
	public function testMoveRepoSuccess()
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
}