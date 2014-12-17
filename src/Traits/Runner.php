<?php

namespace Awooga\Traits;

trait Runner
{
	/**
	 * Fetches results for the given SQL statement
	 * 
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $params
	 * @return array
	 */
	public function fetchResults(\PDO $pdo, $sql, array $params = array())
	{
		return $this->runStatement($pdo, $sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Fetches a single column for the given SQL statement
	 * 
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function fetchColumn(\PDO $pdo, $sql, array $params = array())
	{
		return $this->runStatement($pdo, $sql, $params)->fetchColumn();		
	}

	/**
	 * Runs a parameterised piece of SQL and returns the statement object in which it was run
	 * 
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $params
	 * @return \PDOStatement
	 */
	protected function runStatement(\PDO $pdo, $sql, array $params = array())
	{
		$statement = $pdo->prepare($sql);
		$statement->execute($params);

		return $statement;
	}

	/**
	 * Runs SQL, throwing an error if it does not exist, and returns the statement object
	 * 
	 * @param \PDO $pdo
	 * @param string $sql
	 * @param array $params
	 * @return \PDOStatement
	 * @throws \Exception
	 */
	protected function runStatementWithException(\PDO $pdo, $sql, array $params = array())
	{
		$statement = $pdo->prepare($sql);
		$ok = $statement->execute($params);

		if ($ok === false)
		{
			throw new \Exception(
				"Database call failed:" . print_r($statement->errorInfo(), true)
			);
		}

		return $statement;		
	}
}
