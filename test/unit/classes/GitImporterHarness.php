<?php

namespace Awooga\Testing;

/**
 * This class inherits from the real GitImporter, making it more amenable to testing
 */
class GitImporterHarness extends \Awooga\GitImporter
{
	protected $nextGitFails = false;

	protected function makeGitFail()
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
	 * Creates a relative path that points to a test repo
	 * 
	 * @param type $url
	 * @return type
	 */
	protected function getCheckoutPath($url)
	{
		return parent::getCheckoutPath($url);
	}
}
