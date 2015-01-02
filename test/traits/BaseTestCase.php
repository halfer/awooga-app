<?php

namespace Awooga\Testing;

trait BaseTestCase
{
	use \Awooga\Traits\Config;

	/**
	 * Tries to run the SQL statements in the specified file
	 * 
	 * @throws \Exception
	 * @param string $sqlPath
	 */
	protected function runSqlFile(\PDO $pdo, $sqlPath)
	{
		$sql = file_get_contents($sqlPath);

		$this->runSql($pdo, $sql);
	}

	/**
	 * Tries to run the SQL statements in the supplied string
	 * 
	 * @throws \Exception
	 * @param string $sql
	 */
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

	/**
	 * Gets a PDO driver
	 * 
	 * @todo This copies the initDriver code in BaseController, can we move to Database trait?
	 * 
	 * @param boolean $selectDatabase
	 * @return \PDO
	 */
	protected function getDriver($selectDatabase = true)
	{
		// Get database settings
		$config = $this->getEnvConfig('database');

		// Connect to the database
		$database = $selectDatabase ? "dbname={$config['database']};" : '';
		$dsn = "mysql:{$database}host={$config['host']};username={$config['username']};password={$config['password']}";
		$pdo = new \PDO($dsn, $config['username'], $config['password']);

		return $pdo;
	}

	/**
	 * Retrieves the specified config value from environment-specific settings
	 * 
	 * @param string $key
	 * @return string
	 */
	protected function getEnvConfig($key)
	{
		return $this->getEnvConfigForMode('test', $key);
	}


	/**
	 * Gets the path for the project root
	 * 
	 * @return string
	 */
	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../..');
	}
}