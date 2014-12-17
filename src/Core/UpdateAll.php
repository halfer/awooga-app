<?php

namespace Awooga\Core;

class UpdateAll
{
	protected $importer;

	use Database;

	/**
	 * Constructor for this class
	 */
	public function __construct(GitImporter $importer = null)
	{
		$this->importer = $importer;
	}

	public function run($limit = 20, $sleep = true)
	{
		// Only create a run if one is required
		list($runId, $repoRows) = $this->getNextRepos($limit);
		if (!$runId)
		{
			$runId = $this->createRun();
		}

		$importer = $this->getImporter();
		$importer->setRunId($runId);

		// Import each repository
		foreach ($repoRows as $repoRow)
		{
			$importer->processRepo($repoRow['id'], $repoRow['url']);
			if ($sleep)
			{
				sleep(2);
			}
		}
	}

	/**
	 * Gets the next N repo rows
	 * 
	 * @todo Make this protected again
	 * 
	 * @param integer $limit
	 */
	public function getNextRepos($limit)
	{
		list($lastRunId, $lastRepoId) = $this->findLastRunAndRepo();
		$repoRows = $this->fetchRemainingRows($lastRepoId, $limit);

		// If there are no rows, read from the start,
		// and null the run, since we need a new one
		if (!$repoRows)
		{
			$repoRows = $this->getFirstRepos($limit);
			$lastRunId = null;
		}

		return array($lastRunId, $repoRows);
	}

	protected function findLastRunAndRepo()
	{
		// Deliberately not filtering by success, so as not to prioritise failed repos
		$sql = "
			SELECT
				run_id,
				repository_id
			FROM
				repository_log
			ORDER BY
				run_id DESC, repository_id DESC
			LIMIT 1
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		if (!$ok)
		{
			throw new \Exception("Could not get last run/repo information");
		}

		$row = $statement->fetch(\PDO::FETCH_ASSOC);

		return array(
			isset($row['run_id']) ? $row['run_id'] : null,
			isset($row['repository_id']) ? $row['repository_id'] : null,
		);
	}

	/**
	 * Fetches any remaining repos in the current run
	 * 
	 * @param integer $fromRepoId
	 * @param integer $limit
	 */
	protected function fetchRemainingRows($fromRepoId, $limit)
	{
		$intLimit = (int) $limit;
		$sql = "
			SELECT *
			FROM
				repository
			WHERE
				is_enabled = 1
				AND id > :repo_id
				AND (
					due_at IS NULL
					OR due_at < :now
				)
			LIMIT $intLimit
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(
			array(':repo_id' => $fromRepoId, ':now' => $this->getCurrentDateTime(), )
		);
		if (!$ok)
		{
			// @todo Throw an exception here please
			print_r($statement->errorInfo(), true);
		}

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Fetches the next set of repos, starting from the start again
	 * 
	 * @param integer $limit
	 */
	protected function getFirstRepos($limit)
	{
		// @todo Can this share some SQL with fetchRemainingRows?
		$intLimit = (int) $limit;
		$sql = "
			SELECT *
			FROM repository
			WHERE
				is_enabled = 1			
				AND (
					due_at IS NULL
					OR due_at < :now
				)
			LIMIT $intLimit
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':now' => $this->getCurrentDateTime(), ));

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
			array(':created_at' => $this->getCurrentDateTime(), )
		);

		if (!$ok)
		{
			throw new \Exception(
				"Run could not be created"
			);
		}

		return $pdo->lastInsertId();
	}

	protected function getImporter()
	{
		if (!$this->importer)
		{
			throw new \Exception("No importer set");
		}

		return $this->importer;
	}

	/**
	 * Gets the current date and time, in SQL format
	 * 
	 * @return string
	 */
	protected function getCurrentDateTime()
	{
		return (new \DateTime())->format('Y-m-d H:i:s');
	}
}
