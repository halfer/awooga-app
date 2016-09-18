<?php

namespace Awooga\Testing\Browser;

class TestListener extends \halfer\SpiderlingUtils\TestListener
{
	protected $hasInitialised = false;

	/**
	 * Specifies which suites and namespaces to respond to
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function switchOnBySuiteName($name)
	{
		return
			($name == 'browser') ||
			(strpos($name, 'Awooga\\Testing\\Browser\\') !== false);
	}

	public function runningBrowserTests()
	{
		parent::runningBrowserTests();
		$this->removeSearchIndex();
	}

	protected function removeSearchIndex()
	{
		system('rm -rf ' . $this->getProjectRoot() . '/filesystem/tmp/search-index');
	}
}
