<?php

namespace Awooga\Testing;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Common library loading for all test classes
	 */
	public function setUp()
	{
		$root = $this->getProjectRoot();
		require_once $root . '/src/classes/Report.php';
		require_once $root . '/src/classes/Exceptions/SeriousException.php';
		require_once $root . '/src/classes/Exceptions/TrivialException.php';		
	}

	/**
	 * Gets a PDO driver
	 * 
	 * @return \PDO
	 */
	protected function getDriver()
	{
		// Connect to the database
		// @todo Pull this from env config
		$dsn = 'mysql:dbname=awooga_test;host=localhost;username=awooga_user_test;password=password';
		$pdo = new \PDO($dsn, 'awooga_user_test', 'password');

		return $pdo;
	}

	/**
	 * Creates the test database
	 * 
	 * @param \PDO $pdo
	 * @return integer Repository ID
	 */
	protected function buildDatabase(\PDO $pdo)
	{
		$this->runSql($pdo, $this->getProjectRoot() . '/test/build/init.sql');
		$this->runSql($pdo, $this->getProjectRoot() . '/build/create.sql');
		$repoId = $this->buildRepo($pdo, 1);

		return $repoId;
	}

	/**
	 * Creates a dummy repo account (hardwired ID for now)
	 * 
	 * @param \PDO $pdo
	 */
	protected function buildRepo(\PDO $pdo)
	{
		$repoId = 1;
		$sql = "
			INSERT INTO
				repository
			(id, url, created_at)
			VALUES ($repoId, 'http://example.com/repo.git', '2014-11-18')
		";
		$pdo->exec($sql);

		return $repoId;
	}

	protected function runSql(\PDO $pdo, $sqlPath)
	{
		$sql = file_get_contents($sqlPath);
		$rows = $pdo->exec($sql);

		if ($rows === false)
		{
			print_r($pdo->errorInfo());
			throw new \Exception(
				"Could not initialise the database"
			);
		}
	}

	public function fetchResults(\PDO $pdo, $sql, array $params)
	{
		return $this->runStatement($pdo, $sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function fetchColumn(\PDO $pdo, $sql, array $params)
	{
		return $this->runStatement($pdo, $sql, $params)->fetchColumn();		
	}

	protected function runStatement(\PDO $pdo, $sql, array $params)
	{
		$statement = $ok = $pdo->prepare($sql);
		$ok = $statement->execute($params);

		return $statement;
	}

	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../../..');
	}
}