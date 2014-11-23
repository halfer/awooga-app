<?php

namespace Awooga;

class UpdateAll
{
	protected $repoRoot;

	use Database;

	/**
	 * Constructor for this class
	 */
	public function __construct(GitImporter $importer = null, $repoRoot = null)
	{
		$this->repoRoot = $repoRoot;
	}

	public function run()
	{
		$repoRows = $this->getNextRepos(20);
		if ($repoRows)
		{
			// Look up last repo id
		}
		else
		{
			$runId = $this->createRun();
			$repoRows = $this->getFirstRepos(20);
		}

		$importer = new GitImporter($runId, $this->repoRoot);

		// Import each repository
		foreach ($repoRows as $repoRow)
		{
			print_r($repoRow);
			//$importer->processRepo($repoId, $url, $oldPath);
			sleep(2);
		}
	}

	/**
	 * Gets the next N repo rows
	 * 
	 * @todo Make this protected again
	 * 
	 * @param integer $n
	 */
	public function getNextRepos($n)
	{
		// I'm deliberately not filtering by success here, so as not to prioritise
		// failed repos
		$sql1 = "
			SELECT repository_id
			FROM repository_log
			ORDER BY run_id DESC, repository_id DESC
			LIMIT 1
		";
		$statement = $this->getDriver()->prepare($sql1);
		$ok1 = $statement->execute();
		$lastRepoId = $statement->fetchColumn();

		// Now let's get any rows after this
		$intLimit = (int) $n;
		$sql2 = "
			SELECT *
			FROM repository
			WHERE
				is_enabled = 1
				AND id > :repo_id
			LIMIT $intLimit
		";
		$statement2 = $this->getDriver()->prepare($sql2);
		$ok2 = $statement2->execute(array(':repo_id' => $lastRepoId, ));
		if (!$ok2)
		{
			print_r($statement->errorInfo(), true);
		}

		$repoRows = $statement2->fetchAll();

		return $repoRows;
	}

	protected function getFirstRepos($n)
	{
		$sql = "
			SELECT *
			FROM repository
			WHERE
				is_enabled = 1			
			LIMIT :limit
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':limit' => $n, ));
		$repoRows = $statement->fetchAll();

		return $repoRows;
	}

	/**
	 * Creates a run entry, returns a run ID
	 * 
	 * @return integer Run ID
	 */
	protected function createRun()
	{
		$sql = "
			INSERT INTO run
			(created_at)
			VALUES (:created_at)
		";
		$pdo = $this->getDriver();
		$statement = $pdo->prepare($sql);
		$ok = $statement->execute(
			array(':created_at' => (new \DateTime())->format('Y-m-d H:i:s'), )
		);

		if (!$ok)
		{
			throw new \Exception(
				
			);
		}

		return $pdo->lastInsertId();
	}
}
