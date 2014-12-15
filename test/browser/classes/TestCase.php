<?php

namespace Awooga\Testing\Browser;

// Load the parent relative to dir location
require_once realpath(__DIR__ . '/../..') . '/traits/BaseTestCase.php';

abstract class TestCase extends \Openbuildings\PHPUnitSpiderling\Testcase_Spiderling
{
	use \Awooga\Testing\BaseTestCase;

	const DOMAIN = 'http://localhost:8090';

	/**
	 * Common library loading for all test classes
	 */
	public function setUp()
	{
		$this->buildDatabase($this->getDriver(false));
	}

	/**
	 * Creates the test database
	 * 
	 * @param \PDO $pdo
	 */
	protected function buildDatabase(\PDO $pdo)
	{
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/build/init.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/build/database/create.sql');
		$this->runSqlFile($pdo, $this->getProjectRoot() . '/test/browser/fixtures/data.sql');
	}
}