<?php

namespace Awooga\Testing\Browser;

use halfer\SpiderlingUtils\Server;

class TestListener extends \halfer\SpiderlingUtils\TestListener
{
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

	public function setupServers()
	{
		$projectRoot = $this->getProjectRoot();
		$server = new Server($projectRoot . '/web');
		$server->setRouterScriptPath($projectRoot . '/test/browser/scripts/router.php');
		$server->setCheckAliveUri('/server-check');

		$this->addServer($server);
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

	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../../..');
	}
}
