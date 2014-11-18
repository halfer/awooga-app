<?php

namespace Awooga\Testing;

class GitImporterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Set up database
	 */
	public function setUp()
	{
		$root = realpath(__DIR__ . '/../../..');
	}

	/**
	 * Checks that an ordinary clone works fine
	 */
	public function testCloneSuccess()
	{
		
	}

	/**
	 * Check that git failure in doClone causes an exception
	 */
	public function testCloneGitFailure()
	{
		
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
	 * Checks the repo logger works
	 */
	public function testRepoLog()
	{
		
	}
}