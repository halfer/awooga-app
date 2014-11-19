<?php

namespace Awooga\Testing;

/**
 * This class inherits from the real GitImporter, making it more amenable to testing
 */
class GitImporterHarness extends \Awooga\GitImporter
{
	const SCAN_MODE_NORMAL = 1;
	const SCAN_MODE_COUNT = 2;

	protected $nextGitFails = false;
	protected $nextMoveFails = false;
	protected $reportCount = 0;
	protected $scanMode = self::SCAN_MODE_NORMAL;
	protected $checkoutPath;

	public function makeGitFail()
	{
		$this->nextGitFails = true;
	}

	/**
	 * We can dummy Git, since we've set the path to a test repo anyway
	 * 
	 * @param string $url
	 * @param string $path
	 * @return boolean
	 */
	protected function runGitCommand($url, $path)
	{
		// If we've asked for a failure, provide it
		if ($this->nextGitFails)
		{
			$this->nextGitFails = false;
			return false;
		}

		return true;
	}

	public function makeMoveFail()
	{
		$this->nextMoveFails = true;
	}

	protected function deleteOldRepo($oldPath)
	{
		if ($this->nextMoveFails)
		{
			$this->nextMoveFails = false;
			return false;
		}

		return parent::deleteOldRepo($oldPath);
	}

	public function setScanMode($scanMode)
	{
		$this->scanMode = $scanMode;
	}

	/**
	 * A public entry point for this method
	 * 
	 * @param integer $repoId
	 * @param string $repoPath
	 */
	public function scanRepo($repoId, $repoPath)
	{
		parent::scanRepo($repoId, $repoPath);
	}

	/**
	 * Used to count the reports processed
	 * 
	 * @param integer $repoId
	 * @param string $reportPath
	 */
	public function scanReport($repoId, $reportPath)
	{
		$this->reportCount++;
		parent::scanReport($repoId, $reportPath);
	}

	public function getReportCount()
	{
		return $this->reportCount;
	}

	/**
	 * Returns a relative path that points to a test repo
	 * 
	 * @param string $url
	 * @return string
	 */
	protected function getCheckoutPath($url)
	{
		// Only allow this test harness feature if it's been set
		if (!$this->checkoutPath)
		{
			throw new \Exception(
				"No checkout path set"
			);
		}

		return $this->checkoutPath;
	}

	/**
	 * Resets the checkout path seen by the Git command method
	 * 
	 * @param string $checkoutPath
	 */
	public function setCheckoutPath($checkoutPath)
	{
		$this->checkoutPath = $checkoutPath;
	}
}
