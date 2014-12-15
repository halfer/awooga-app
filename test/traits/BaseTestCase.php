<?php

namespace Awooga\Testing;

trait BaseTestCase
{
	protected function runSqlFile(\PDO $pdo, $sqlPath)
	{
		$sql = file_get_contents($sqlPath);

		return $this->runSql($pdo, $sql);
	}

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
	 * @todo Pull this from env config
	 * 
	 * @param boolean $selectDatabase
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
	 * Gets the path for the project root
	 * 
	 * @return string
	 */
	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../..');
	}
}