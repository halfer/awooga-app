<?php

namespace Awooga;

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
}