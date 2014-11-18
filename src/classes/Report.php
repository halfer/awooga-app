<?php

namespace Awooga;

class Report
{
	protected $repoId;
	protected $title;
	protected $urls;
	protected $description;
	protected $issues;
	protected $notifiedDate;

	protected $pdo;

	/**
	 * Creates this report and attaches it to a specific repo ID
	 * 
	 * @param integer $repoId
	 */
	public function __construct($repoId)
	{
		$this->repoId = $repoId;
	}

	public function setTitle($title)
	{
		$this->isRequired($title);
		$this->isString($title);

		$this->title = $title;
	}

	public function setUrl($url)
	{
		// Turn strings into an array
		if (is_string($url))
		{
			$url = array($url);
		}

		// If the URL is not an array, bomb out
		$formatFail = false;
		if (is_array($url))
		{
			foreach ($url as $urlItem)
			{
				$this->isRequired($urlItem);
				if (!is_string($urlItem))
				{
					$formatFail = true;
				}
			}
		}
		else
		{
			$formatFail = true;
		}

		if ($formatFail)
		{
			throw new Exceptions\TrivialException(
				"URLs must either be a string or an array of strings"
			);
		}

		$this->urls = $url;
	}

	public function setDescription($description)
	{
		$this->isRequired($description);
		$this->isString($description);

		$this->description = $description;
	}

	/**
	 * Setter to accept the issue array
	 * 
	 * @param array $issues
	 */
	public function setIssues($issues)
	{
		$this->isRequired($issues);
		$this->isArray($issues);

		// Valid entries are copied to an output array
		$issuesOut = array();
		foreach ($issues as $issue)
		{
			// If the issue doesn't have a issue_cat_code, bomb out
			if (!isset($issue['issue_cat_code']))
			{
				throw new Exceptions\TrivialException(
					"Issues must have an issue_cat_code entry"
				);
			}

			// If the issue doesn't have a valid code, bomb out also
			if (!$this->validateIssueCatCode($issue['issue_cat_code']))
			{
				throw new Exceptions\TrivialException(
					"Issues must have a valid issue_cat_code"
				);
			}

			if (isset($issue['description']))
			{
				if (!is_string($issue['description']))
				{
					throw new Exceptions\TrivialException(
						'Descriptions must be strings'
					);
				}
			}

			// Strip out empty descriptions and any unrecognised keys
			$issueOut = array(
				'issue_cat_code' => $issue['issue_cat_code'],
			);
			if (isset($issue['description']) && $issue['description'])
			{
				$issueOut['description'] = $issue['description'];
			}
			$issuesOut[] = $issueOut;
		}

		$this->issues = $issuesOut;
	}

	/**
	 * Determines if the passed issue code is valid
	 * 
	 * @param string $catCode
	 * @return boolean
	 */
	protected function validateIssueCatCode($catCode)
	{
		$issueCodes = array(
			'xss', 'sql-injection',
			'password-clear',
			'password-inadequate-hashing',
			'deprecated-library',
			'sql-needs-parameterisation',
			'uncategorised',
		);

		return in_array($catCode, $issueCodes, true);
	}

	/**
	 * Sets an optional author notified date
	 * 
	 * @todo Throw a trivial exception if the date is invalid
	 * 
	 * @param string $notifiedDate
	 */
	public function setAuthorNotifiedDate($notifiedDate)
	{
		$this->isRequired($notifiedDate);
		$this->isString($notifiedDate);

		$notifiedDate = \DateTime::createFromFormat('Y-m-d', $notifiedDate);
		if (!$notifiedDate)
		{
			throw new Exceptions\TrivialException("Invalid author notification date passed");
		}

		$this->notifiedDate = $notifiedDate;
	}

	/**
	 * Saves or re-saves the report
	 * 
	 * Currently I'm deleting issues and URLs and then recreating them, for simplicity. This
	 * will change their PKs, but that's OK since I don't (currently) plan on having anything that
	 * needs to rely on them.
	 */
	public function save()
	{
		$this->validateBeforeSave();

		// See if we are editing a report or creating a new one
		if ($reportId = $this->getCurrentReport())
		{
			// These can be zapped and recreated
			$this->deleteIssues($reportId);
			$this->deleteUrls($reportId);

			// Do update here
			$this->update();
		}
		else
		{
			// Do insert here
			$reportId = $this->insert();
		}

		// (Re)insert issues and URLs
		$this->insertIssues($reportId);
		$this->insertUrls($reportId);
	}

	/**
	 * Removes issues against a report
	 *
	 * @param integer $reportId
	 */
	protected function deleteIssues($reportId)
	{
		return $this->deleteReportThing('report_issue', $reportId);
	}

	/**
	 * Tries to write the current issues against the current report
	 *
	 * @param integer $reportId
	 */
	protected function deleteUrls($reportId)
	{
		return $this->deleteReportThing('report_url', $reportId);
	}

	/**
	 * Deletes things related to a report
	 *
	 * @param string $table
	 * @param integer $reportId
	 */
	protected function deleteReportThing($table, $reportId)
	{
		// For extra safety
		$tableQuoted = $this->getDriver()->quote($table);
		
		$sql = "
			DELETE FROM {$tableQuoted}
			WHERE report_id = :report_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':report_id' => $reportId, ));

		return $ok;
	}

	protected function validateBeforeSave()
	{
		
	}

	/**
	 * Internal save command to do an update
	 *
	 * @param integer $reportId
	 * @return boolean
	 */
	protected function update($reportId)
	{
		$sql = "
			UPDATE report SET
				repository_id = :repo_id,
				title = :title,
				description = :description,
				author_notified_at = :notified_at
			WHERE
				id = :report_id
		";
		
		return $this->runSaveCommand($sql, $reportId);
	}

	/**
	 * Internal save command to do an insert
	 *
	 * @return boolean
	 */
	protected function insert()
	{
		// @todo Why is author_notified_at not being set to null?

		$sql = "
			INSERT INTO report
			(repository_id, title, description, author_notified_at)
			VALUES (:repo_id, :title, :description, :notified_at)
		";

		return $this->runSaveCommand($sql);
	}

	/**
	 * Internal method to run save SQL
	 *
	 * @param string $sql
	 * @param integer $reportId
	 * @return boolean
	 */
	protected function runSaveCommand($sql, $reportId = null)
	{
		// Set up the parameters (the report is for the update only)
		$params = array(
			':repo_id' => $this->repoId,
			':title' => $this->title,
		);
		// Swap these things out for a null if required
		$this->addNullableColumn($params, $sql, ':description', $this->description);
		$this->addNullableColumn($params, $sql, ':notified_at', $this->getAuthorNotifiedDateAsString());

		if ($reportId)
		{
			$params[':report_id'] = $reportId;
		}

		$statement = $this->getDriver()->prepare($sql);

		return $statement->execute($params);
	}

	/**
	 * Swaps out a parameter for a SQL NULL value if required
	 * 
	 * @todo If I use a null value in a execute, does this not swap NULL in correctly?
	 */
	protected function addNullableColumn(array &$params, &$sql, $column, $value)
	{
		if ($value)
		{
			$params[$column] = $value;
		}
		else
		{
			$sql = str_replace($column, 'NULL', $sql);
		}		
	}

	protected function getAuthorNotifiedDateAsString()
	{
		return $this->notifiedDate ? $this->notifiedDate->format('Y-m-d') : null;
	}

	protected function insertIssues($reportId)
	{
		$sqlTemplate = "
			INSERT INTO report_issue
			(report_id, description, issue_id)
			VALUES (:report_id, :description, :issue_id)
		";
		foreach ($this->issues as $issue)
		{
			$params = array(
				':report_id' => $reportId,
				':issue_id' => $this->getIssueIdForCode($issue['issue_cat_code']),
			);
			$sql = $sqlTemplate;
			$this->addNullableColumn(
				$params,
				$sql,
				':description',
				isset($issue['description']) ? $issue['description'] : null
			);
			$statement = $this->getDriver()->prepare($sql);
			$statement->execute($params);
		}
	}

	protected function getIssueIdForCode($code)
	{
		$sql = "SELECT id FROM issue WHERE code = :code";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':code' => $code, ));

		return $statement->fetchColumn();
	}

	protected function insertUrls($reportId)
	{
		$sql = "
			INSERT INTO resource_url
			(report_id, url)
			VALUES (:report_id, :url)
		";
		$statement = $this->getDriver()->prepare($sql);
		foreach ($this->urls as $url)
		{
			$statement->execute(
				array('report_id' => $reportId, 'url' => $url, )
			);
		}
	}

	/**
	 * Check if we need to do an update rather than an insert
	 * 
	 * Search for all the URLs for this repo. If they point to more than one report,
	 * then let's chuck it out with a trivial exception. Hopefully one of the other
	 * reports will end up deleted and it will work out on the next pass.
	 */
	protected function getCurrentReport()
	{
		// Set up the test for each URL
		$sql = "
			SELECT r.id report_id
			FROM report r
			INNER JOIN resource_url u ON (r.id = u.report_id)
			WHERE
				r.repository_id = :repo_id
				AND u.url = :url
		";
		$statement = $this->getDriver()->prepare($sql);

		$reportId = null;
		foreach ($this->urls as $url)
		{
			$row = $statement->execute(
				array(':repo_id' => $this->repoId, ':url' => $url, )
			);
			// If we have a report ID, check this is not different
			if ($reportId)
			{
				if ($row['report_id'] != $reportId)
				{
					throw new Exceptions\TrivialException(
						"URLs split over multiple reports cannot appear on the same report"
					);
				}
			}
			else
			{
				$reportId = $row['report_id'];
			}
		}

		return $reportId;
	}

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

	protected function isString($string)
	{
		if (!is_string($string))
		{
			throw new Exceptions\TrivialException("This field is expected to be a string");			
		}
	}

	protected function isArray($array)
	{
		if (!is_array($array))
		{
			throw new Exceptions\TrivialException("This field is expected to be an array");			
		}		
	}

	protected function isRequired($data)
	{
		if (!$data)
		{
			throw new Exceptions\TrivialException("This field is required");
		}		
	}
}