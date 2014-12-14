<?php

namespace Awooga\Testing\Browser;

abstract class TestCase extends \Openbuildings\PHPUnitSpiderling\Testcase_Spiderling
{
	/**
	 * Common library loading for all test classes
	 */
	public function setUp()
	{
		$this->buildDatabase($this->getDriver(false));
	}

	/**
	 * Gets a PDO driver
	 * 
	 * @todo Pull this from env config
	 * @todo Copied from unit/classes/TestCase
	 * 
	 * @return \PDO
	 */
	protected function getDriver($selectDatabase = true)
	{
		// Connect to the database
		$database = $selectDatabase ? 'dbname=awooga_test;' : '';
		$dsn = "mysql:{$database}host=localhost;username=awooga_user_test;password=password";
		$pdo = new \PDO($dsn, 'awooga_user_test', 'password');

		return $pdo;
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

	// @todo Copied from unit/classes/TestCase
	protected function runSqlFile(\PDO $pdo, $sqlPath)
	{
		$sql = file_get_contents($sqlPath);

		return $this->runSql($pdo, $sql);
	}

	// @todo Copied from unit/classes/TestCase
	protected function runSql(\PDO $pdo, $sql)
	{
		$rows = $pdo->exec($sql);

		if ($rows === false)
		{
			throw new \Exception(
				"Could not initialise the database"
			);
		}
	}

	// @todo Copied from unit/classes/TestCase
	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../../..');
	}
}