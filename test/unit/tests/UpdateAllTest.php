<?php

namespace Awooga\Testing;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/..') . '/classes/TestCase.php';

class UpdateAllTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();

		$root = $this->getProjectRoot();
		require_once $root . '/src/classes/UpdateAll.php';
		require_once $root . '/test/unit/classes/UpdateAllTestHarness.php';
	}

	public function testCreateRun()
	{
		$this->buildDatabase($this->getDriver(false));
		$pdo = $this->getDriver();

		$updater = new UpdateAllTestHarness();
		$updater->setDriver($pdo);
		$runId = $updater->createRun();

		$this->assertNotNull(
			$runId,
			"Ensuring a run ID can be generated"
		);
	}

	public function testUpdateSimple()
	{
		
	}

	public function testGetNext()
	{
		
	}
}