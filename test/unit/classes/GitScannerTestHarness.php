<?php

namespace Awooga\Testing\Unit;

/**
 * This class inherits from the real GitScanner, making it more amenable to testing
 */
class GitScannerTestHarness extends \Awooga\Core\GitScanner
{
	protected $reportCount = 0;

	/**
	 * Used to count the reports processed
	 * 
	 * @param string $reportPath
	 */
	public function scanReport($reportPath)
	{
		$this->reportCount++;

		return parent::scanReport($reportPath);
	}

	public function getReportCount()
	{
		return $this->reportCount;
	}

	/**
	 * Grants public access level
	 * 
	 * @param string $oldPath
	 * @return boolean
	 */
	public function deleteOldRepo($oldPath)
	{
		return parent::deleteOldRepo($oldPath);
	}

}