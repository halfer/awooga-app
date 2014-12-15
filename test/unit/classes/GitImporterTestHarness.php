<?php

namespace Awooga\Testing\Unit;

/**
 * This class inherits from the real GitImporter, making it more amenable to testing
 */
class GitImporterTestHarness extends \Awooga\Core\GitImporter
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
	 * In test mode we treat the URL as a file source
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

		// The URL needs to be a folder
		if (!is_dir($url))
		{
			throw new \Exception("For testing, the URL ($url) should be a source directory");
		}
		if (!$path)
		{
			throw new \Exception("For testing, the path ($path) should be the target directory");
		}

		$output = $return = null;
		exec(
			$command = "cp --recursive {$url} {$path}",
			$output, $return
		);
		if ($return)
		{
			throw new \Exception(
				"An error occured doing a fake Git checkout"
			);
		}

		return true;
	}

	public function makeMoveFail()
	{
		$this->nextMoveFails = true;
	}

	/**
	 * Fail-enabled delete operation, also grants public access level
	 * 
	 * @param string $oldPath
	 * @return boolean
	 */
	public function deleteOldRepo($oldPath)
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
	 * A public entry point for the clone method
	 * 
	 * @param string $url
	 */
	public function cloneRepo($url)
	{
		return parent::cloneRepo($url);
	}

	/**
	 * A public entry point for the move method
	 * 
	 * @param integer $repoId
	 * @param string $newPath
	 */
	public function moveRepo($repoId, $newPath)
	{
		return parent::moveRepo($repoId, $newPath);
	}
	/**
	 * A public entry point for the scan method
	 * 
	 * @param integer $repoId
	 * @param string $repoPath
	 */
	public function scanRepo($repoId, $repoPath)
	{
		return parent::scanRepo($repoId, $repoPath);
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

		return parent::scanReport($repoId, $reportPath);
	}

	public function getReportCount()
	{
		return $this->reportCount;
	}

	/**
	 * A public entry point for the reschedule method
	 * 
	 * @param integer $repoId
	 * @param boolean $wasSuccessful
	 * @return boolean True if successful
	 */
	public function rescheduleRepo($repoId, $wasSuccessful)
	{
		return parent::rescheduleRepo($repoId, $wasSuccessful);
	}

	/**
	 * A public entry point for the error count method
	 * 
	 * @param integer $repoId
	 * @return integer
	 */
	public function countRecentFails($repoId)
	{
		return parent::countRecentFails($repoId);
	}

	/**
	 * A public entry point for the logging method
	 * 
	 * @param integer $repoId
	 * @param string $logType
	 * @param string $message
	 * @param string $logLevel
	 */
	public function repoLog($repoId, $logType, $message = null, $logLevel = self::LOG_LEVEL_SUCCESS)
	{
		return parent::repoLog($repoId, $logType, $message, $logLevel);
	}

	/**
	 * Returns a relative path that points to a test repo, or passes to parent
	 * 
	 * @param string $url
	 * @return string
	 */
	protected function getCheckoutPath($url)
	{
		return $this->checkoutPath ? $this->checkoutPath : parent::getCheckoutPath($url);
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
