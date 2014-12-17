<?php

namespace Awooga\Core;

use \Awooga\Exceptions\TrivialException;

class Report
{
	protected $repoId;
	protected $id;
	protected $title;
	protected $urls;
	protected $description;
	protected $descriptionHtml;
	protected $issues;
	protected $notifiedDate;

	use Database;

	/**
	 * Creates this report and attaches it to a specific repo ID
	 * 
	 * @param integer $repoId
	 */
	public function __construct($repoId)
	{
		$this->repoId = $repoId;
	}

	/**
	 * Sets a string title
	 * 
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->isRequired($title, 'title');
		$this->isString($title);

		$this->title = $title;
	}

	/**
	 * Sets a URL or an array of URLs
	 * 
	 * @param string|array $url
	 * @throws TrivialException
	 */
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
				$this->isRequired($urlItem, 'url');
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
			throw new TrivialException(
				"URLs must either be a string or an array of strings"
			);
		}

		// Check for duplicates, these are not allowed
		if (array_unique($url) != $url)
		{
			throw new TrivialException(
				"URL arrays may not contain duplicates"
			);			
		}

		$this->urls = $url;
	}

	/**
	 * Sets a string description
	 * 
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->isRequired($description, 'description');
		$this->isString($description);

		$this->description = $description;
	}

	/**
	 * Setter to accept the issue array
	 * 
	 * This is deliberately not array-hinted, so we can accept any JSON value and bork gracefully
	 * 
	 * @param array $issues
	 */
	public function setIssues($issues)
	{
		$this->isRequired($issues, 'issues array');
		$this->isArray($issues);

		// Valid entries are copied to an output array
		$issuesOut = array();

		// Keep track of issue codes, to detect dups
		$issueCodes = array();

		foreach ($issues as $issue)
		{
			// Throw exception if this issue fails validation
			$this->validateIssue($issue);

			// Add the issue code to the list
			$issueCode = $issue['issue_cat_code'];
			$issueCodes[] = $issueCode;

			// Strip out empty descriptions and any unrecognised keys
			$issueOut = array('issue_cat_code' => $issueCode, );
			if (isset($issue['description']) && $issue['description'])
			{
				$issueOut['description'] = $issue['description'];
			}
			if (isset($issue['resolved_at']))
			{
				$issueOut['resolved_at'] = $issue['resolved_at'];
			}
			$issuesOut[] = $issueOut;
		}

		// Check for duplicates, these are not allowed
		if (array_unique($issueCodes) != $issueCodes)
		{
			throw new TrivialException(
				"Issue codes may not be duplicated in a report"
			);			
		}

		$this->issues = $issuesOut;
	}

	/**
	 * Checks a single issue from an issue array
	 * 
	 * @param array $issue
	 * @throws TrivialException
	 */
	protected function validateIssue($issue)
	{
		// If the issue doesn't have a issue_cat_code, bomb out
		if (!isset($issue['issue_cat_code']))
		{
			throw new TrivialException("Issues must have an issue_cat_code entry");
		}

		// If the issue doesn't have a valid code, bomb out also
		$issueCode = $issue['issue_cat_code'];
		if (!$this->validateIssueCatCode($issueCode))
		{
			$issueCodeShort = substr($issueCode, 0, 50);
			throw new TrivialException(
				"'{$issueCodeShort}' does not seem to be a valid issue category code"
			);
		}

		if (isset($issue['description']))
		{
			if (!is_string($issue['description']))
			{
				throw new TrivialException('Descriptions must be strings');
			}
		}

		if (isset($issue['resolved_at']))
		{
			$date = \DateTime::createFromFormat('Y-m-d', $issue['resolved_at']);
			if ($date === false || $this->getLastDateParseFailCount())
			{
				throw new TrivialException(
					'A resolution date must be in the form yyyy-mm-dd'
				);					
			}
		}
	}

	protected function getLastDateParseFailCount()
	{
		$fails = \DateTime::getLastErrors();
		$warnings = isset($fails['warning_count']) ? $fails['warning_count'] : 0;
		$errors = isset($fails['error_count']) ? $fails['error_count'] : 0;

		return $warnings + $errors;
	}

	/**
	 * Determines if the passed issue code is valid
	 * 
	 * @param string $catCode
	 * @return boolean
	 */
	protected function validateIssueCatCode($catCode)
	{
		$sql = "
			SELECT 1 FROM
			issue
			WHERE code = :issue_code
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':issue_code' => $catCode, ));
		if (!$ok)
		{
			throw new \Exception();
		}

		return is_array($statement->fetch());
	}

	/**
	 * Sets an optional author notified date
	 * 
	 * @param string $notifiedDate
	 */
	public function setAuthorNotifiedDate($notifiedDate)
	{
		if ($notifiedDate)
		{
			$this->isString($notifiedDate);

			$notifiedDate = \DateTime::createFromFormat('Y-m-d', $notifiedDate);
			if (!$notifiedDate || $this->getLastDateParseFailCount())
			{
				throw new TrivialException("Invalid author notification date passed");
			}
		}

		$this->notifiedDate = $notifiedDate ? $notifiedDate : null;
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
			$this->update($reportId);
		}
		else
		{
			// Do insert here
			$reportId = $this->insert();
		}

		// (Re)insert issues and URLs
		$this->insertIssues($reportId);
		$this->insertUrls($reportId);

		$this->id = $reportId;

		return $reportId;
	}

	public function index(Searcher $searcher)
	{
		$searcher->index(
			array(
				'id' => $this->id,
				'title' => $this->title,
				'description_html' => $this->descriptionHtml
			),
			$this->urls,
			$this->issues
		);
	}

	/**
	 * Removes issues against a report
	 *
	 * @param integer $reportId
	 */
	protected function deleteIssues($reportId)
	{
		$this->deleteReportThing('report_issue', $reportId);
	}

	/**
	 * Tries to write the current issues against the current report
	 *
	 * @param integer $reportId
	 */
	protected function deleteUrls($reportId)
	{
		$this->deleteReportThing('resource_url', $reportId);
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
		$tableUntainted = preg_replace('/[^A-Z_]/i', '', $table);
		
		$sql = "
			DELETE FROM {$tableUntainted}
			WHERE report_id = :report_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':report_id' => $reportId, ));

		// Bork if there is an issue
		if ($ok === false)
		{
			throw new \Awooga\Exceptions\SeriousException(
				"Could not delete rows from $tableUntainted"
			);
		}
	}

	/**
	 * Uses the setter validations to prevent incomplete reports from being saved
	 */
	protected function validateBeforeSave()
	{
		$this->setTitle($this->title);
		$this->setDescription($this->description);
		$this->setUrl($this->urls);
		$this->setIssues($this->issues);
	}

	/**
	 * Internal save command to do an update
	 *
	 * @param integer $reportId
	 * @return boolean|null
	 * @throws TrivialException
	 */
	protected function update($reportId)
	{
		$sql = "
			UPDATE report SET
				repository_id = :repo_id,
				title = :title,
				description = :description,
				description_html = :description_html,
				author_notified_at = :notified_at
			WHERE
				id = :report_id
		";
		
		return $this->runSaveCommand($sql, $reportId);
	}

	/**
	 * Internal save command to do an insert
	 *
	 * @return string
	 * @throws TrivialException
	 */
	protected function insert()
	{
		$sql = "
			INSERT INTO report
			(repository_id, title, description, description_html, author_notified_at)
			VALUES (:repo_id, :title, :description, :description_html, :notified_at)
		";

		$this->runSaveCommand($sql);

		return $this->getDriver()->lastInsertId();
	}

	/**
	 * Internal method to run save SQL
	 *
	 * @param string $sql
	 * @param integer $reportId
	 * @throws TrivialException
	 */
	protected function runSaveCommand($sql, $reportId = null)
	{
		// Set up the parameters (the report is for the update only)
		$this->descriptionHtml = $this->convertFromMarkdown($this->description);
		$params = array(
			':repo_id' => $this->repoId,
			':title' => $this->title,
			':description' => $this->description,
			':description_html' => $this->descriptionHtml,
			':notified_at' => $this->getAuthorNotifiedDateAsString(),
		);

		if ($reportId)
		{
			$params[':report_id'] = $reportId;
		}

		$statement = $this->getDriver()->prepare($sql);

		// Run command and check result
		$ok = $statement->execute($params);
		if ($ok === false)
		{
			throw new TrivialException('Save operation failed');
		}
	}

	/**
	 * Returns the author notified date in YYYY-mm-dd format, or null
	 * 
	 * @return string
	 */
	protected function getAuthorNotifiedDateAsString()
	{
		return $this->notifiedDate ? $this->notifiedDate->format('Y-m-d') : null;
	}

	/**
	 * Inserts issues against the specified report ID
	 * 
	 * @param integer $reportId
	 */
	protected function insertIssues($reportId)
	{
		$sql = "
			INSERT INTO report_issue
			(report_id, description, description_html, issue_id, resolved_at)
			VALUES (:report_id, :description, :description_html, :issue_id, :resolved_at)
		";
		foreach ($this->issues as &$issue)
		{
			$description = isset($issue['description']) && $issue['description'] ?
				$issue['description'] :
				null;
			$resolvedAt = isset($issue['resolved_at']) && $issue['resolved_at'] ?
				$issue['resolved_at'] :
				null;
			$issue['description_html'] = $this->convertFromMarkdown($description);
			$params = array(
				':report_id' => $reportId,
				':issue_id' => $this->getIssueIdForCode($issue['issue_cat_code']),
				':description' => $description,
				':description_html' => $issue['description_html'],
				':resolved_at' => $resolvedAt,
			);
			$statement = $this->getDriver()->prepare($sql);
			$statement->execute($params);
		}
	}

	/**
	 * Converts an issue code into an ID
	 * 
	 * @param string $code
	 * @return string
	 */
	protected function getIssueIdForCode($code)
	{
		$sql = "SELECT id FROM issue WHERE code = :code";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':code' => $code, ));

		return $statement->fetchColumn();
	}

	/**
	 * Inserts the current URLs against the specified report ID
	 * 
	 * @param integer $reportId
	 */
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
	 * 
	 * @return integer
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
			$statement->execute(
				array(':repo_id' => $this->repoId, ':url' => $url, )
			);
			// If we have some rows returned from the query
			if ($statement->rowCount())
			{
				$row = $statement->fetch(\PDO::FETCH_ASSOC);
				// If we have already encountered a report ID, check this is not different
				if ($reportId)
				{
					if ($row['report_id'] != $reportId)
					{
						throw new TrivialException(
							"URLs split over multiple reports cannot be updated by a single report"
						);
					}
				}
				$reportId = $row['report_id'];
			}
		}

		return $reportId;
	}

	/**
	 * Converts a Markdown string to HTML
	 * 
	 * @param string $markdown
	 * @return string|null
	 */
	protected function convertFromMarkdown($markdown)
	{
		return $markdown ? \Michelf\Markdown::defaultTransform($markdown) : null;
	}

	/**
	 * Checks to see if the parameter is a string, raises a TrivialException if not
	 * 
	 * @param mixed $string
	 */
	protected function isString($string)
	{
		if (!is_string($string))
		{
			throw new TrivialException("This field is expected to be a string");			
		}
	}

	/**
	 * Checks to see if the parameter is an array, raises a TrivialException if not
	 * 
	 * @param mixed $array
	 * @throws TrivialException
	 */
	protected function isArray($array)
	{
		if (!is_array($array))
		{
			throw new TrivialException("This field is expected to be an array");			
		}		
	}

	/**
	 * Checks to see if a required field is entered, raises a TrivialException if not
	 * 
	 * @param mixed $data
	 * @param string $name
	 * @throws TrivialException
	 */
	protected function isRequired($data, $name)
	{
		if (!$data)
		{
			throw new TrivialException("The '{$name}' field is required");
		}		
	}
}
