<?php

namespace Awooga\Testing\Unit;

class RepoBuilder
{
	use \Awooga\Traits\Database;

	/**
	 * Creates a dummy repo account
	 * 
	 * @param integer $repoId
	 * @param string $url
	 * @return integer
	 */
	public function create($repoId = 1, $url = null)
	{
		// If the URL has not been supplied, use a default
		if (!$url)
		{
			$url = 'http://example.com/repo.git';
		}

		$sql = "
			INSERT INTO
				repository
			(id, url, created_at)
			VALUES (:repo_id, :url, '2014-11-18')
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(
				':repo_id' => $repoId,
				':url' => $url,
			)
		);

		// Bork if the query fails (e.g. PK clash)
		if (!$ok)
		{
			throw new \Exception(
				"Creating a repository row failed:" . print_r($statement->errorInfo(), true)
			);
		}

		return $repoId;
	}

	/**
	 * Creates a new user
	 * 
	 * @todo Rename the class, as it builds more than repos now
	 * 
	 * @return integer
	 * @throws \Exception
	 */
	public function createUser()
	{
		$sql = "
			INSERT INTO
				user
			(last_login_at)
			VALUES ('2014-12-29')
		";
		$pdo = $this->getDriver();
		$statement = $pdo->prepare($sql);
		$ok = $statement->execute();

		// Bork if the query fails (e.g. PK clash)
		if (!$ok)
		{
			throw new \Exception(
				"Creating a repository row failed:" . print_r($statement->errorInfo(), true)
			);
		}

		return $pdo->lastInsertId();
	}
}