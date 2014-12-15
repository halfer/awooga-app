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
	 * Gets the path for the project root
	 * 
	 * @return string
	 */
	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../..');
	}
}