<?php

namespace Awooga\Traits;

trait Database
{
	protected $pdo;

	/**
	 * Sets the PDO driver
	 * 
	 * @param \PDO $pdo
	 */
	public function setDriver(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * Gets the PDO object
	 * 
	 * @return \PDO
	 */
	protected function getDriver()
	{
		// Bork if no driver is set
		if (!$this->pdo)
		{
			throw new \Exception("No driver has been supplied");
		}

		return $this->pdo;
	}

	/**
	 * Returns all rows for the given SQL
	 * 
	 * (The callers of this method currently assume it will be successful)
	 * 
	 * @todo This duplicates Runner->fetchResults, decide which one we are keeping!
	 * 
	 * @return array
	 */
	protected function fetchAll($sql)
	{
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}