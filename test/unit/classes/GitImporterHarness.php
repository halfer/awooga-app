<?php

namespace Awooga\Testing;

/**
 * This class inherits from the real GitImporter, making it more amenable to testing
 */
class GitImporterHarness extends \Awooga\GitImporter
{
	protected $nextGitFails = false;
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
