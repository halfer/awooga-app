<?php

namespace Awooga\Core;

use \Awooga\Exceptions\TrivialException;
use \Awooga\Exceptions\SeriousException;
use HTMLPurifier_Config, HTMLPurifier;

class Report
{
	const LENGTH_TITLE = 256;
	const LENGTH_DESCRIPTION = 1024;
	const LENGTH_URL = 256;

	protected $repoId;
	protected $userId;
	protected $id;
	protected $title;
	protected $urls;
	protected $description;
	protected $descriptionHtml;
	protected $issues;
	protected $notifiedDate;

	use \Awooga\Traits\Database;
	use \Awooga\Traits\Runner;
	use \Awooga\Traits\Validation;

	/**
	 * Creates this report and attaches it to a specific repo ID
	 * 
	 * @param integer $repoId
	 * @param integer $userId
	 */
	public function __construct($repoId, $userId = null)
	{
		$this->repoId = $repoId;
		$this->userId = $userId;
	}

	/**
	 * Sets a string title
	 * 
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->setRequiredString('title', $title, self::LENGTH_TITLE);
	}

	/**
	 * Sets a string description
	 * 
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->setRequiredString('description', $description, self::LENGTH_DESCRIPTION);
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
				$this->validateLength($urlItem, 'url', self::LENGTH_URL);
				if (!is_string($urlItem))
				{
					$formatFail = true;
				}

				// Check the URL has a protocol
				$hasProtocol = preg_match('#^https?://#', $urlItem);
				if (!$hasProtocol)
				{
					throw new TrivialException(
						"The URL \"" . substr($urlItem, 0, 1024) . "\" does not have a recognised protocol"
					);
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
			if (isset($issue['resolved_at']) && $issue['resolved_at'])
			{
				$issueOut['resolved_at'] = $issue['resolved_at'];
			}
			$issuesOut[] = $issueOut;
		}

		// Check for duplicates, these are not allowed
		if ($this->containsDuplicateCodes($issueCodes))
		{
			throw new TrivialException(
				"Issue codes (other than 'uncategorised') may only appear once in a report"
			);			
		}

		$this->issues = $issuesOut;
	}

	protected function containsDuplicateCodes(array $issueCodes)
	{
		$withoutUncat = array_filter(
			$issueCodes,
			function($value) { return $value != 'uncategorised'; }
		);

		return array_unique($withoutUncat) != $withoutUncat;
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
			$this->validateLength($issue['description'], 'issue-description', self::LENGTH_DESCRIPTION);
		}

		if (isset($issue['resolved_at']) && $issue['resolved_at'])
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
		$statement = $this->runStatement(
			$this->getDriver(),
			"SELECT 1 FROM issue WHERE code = :issue_code",
			array(':issue_code' => $catCode, )
		);
		if ($statement === false)
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
				throw new TrivialException("An author notified date must be in the form yyyy-mm-dd");
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
	 * 
	 * @param integer $reportId Supply this to overwrite a particular row
	 * @return integer
	 */
	public function save($reportId = null)
	{
		$this->validateBeforeSave();

		// See if we need to overwrite
		if ($reportId === null)
		{
			$reportId = $this->getCurrentReport();
		}

		// See if we need to overwrite a report
		if ($reportId)
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

		$statement = $this->runStatement(
			$this->getDriver(),
			"DELETE FROM {$tableUntainted} WHERE report_id = :report_id",
			array(':report_id' => $reportId, )
		);

		// Bork if there is an issue
		if ($statement === false)
		{
			throw new SeriousException(
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
		$this->checkForeignKeys();
	}

	protected function checkForeignKeys()
	{
		if (!$this->repoId && !$this->userId)
		{
			throw new SeriousException(
				"Neither a repo or a user ID has been set in this report"
			);
		}

		if ($this->repoId && $this->userId)
		{
			throw new SeriousException(
				"This report can only be associated with either a repo or a user"
			);			
		}
	}

	/**
	 * Internal save command to do an update
	 *
	 * @param integer $reportId
	 * @throws TrivialException
	 */
	protected function update($reportId)
	{
		$sql = "
			UPDATE report SET
				repository_id = :repo_id,
				user_id = :user_id,
				title = :title,
				description = :description,
				description_html = :description_html,
				author_notified_at = :notified_at
			WHERE
				id = :report_id
		";
		
		$this->runSaveCommand($sql, $reportId);
	}

	/**
	 * Internal save command to do an insert
	 *
	 * @throws TrivialException
	 * @return integer
	 */
	protected function insert()
	{
		$sql = "
			INSERT INTO report
			(repository_id, user_id, title, description, description_html, author_notified_at)
			VALUES (:repo_id, :user_id, :title, :description, :description_html, :notified_at)
		";

		$this->runSaveCommand($sql);

		return (int) $this->getDriver()->lastInsertId();
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
			':user_id' => $this->userId,
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
		$statement = $this->getDriver()->prepare($sql);

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
		return $this->fetchColumn(
			$this->getDriver(),
			"SELECT id FROM issue WHERE code = :code",
			array(':code' => $code, )
		);
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
	 * Search for all the URLs for this repo/user. If they point to more than one report,
	 * then let's chuck it out with a trivial exception. Hopefully one of the other
	 * reports will end up deleted and it will work out on the next pass.
	 * 
	 * @return integer
	 */
	protected function getCurrentReport()
	{
		// Set up the test for each URL
		$filter = $this->repoId ? 'r.repository_id = :repo_id' : 'r.user_id = :user_id';
		$sql = "
			SELECT r.id report_id
			FROM report r
			INNER JOIN resource_url u ON (r.id = u.report_id)
			WHERE
				{$filter} AND u.url = :url
		";
		$statement = $this->getDriver()->prepare($sql);

		$reportId = null;
		foreach ($this->urls as $url)
		{
			// Use either the repo or user ID as a filter
			$statement->execute(
				array(':url' => $url, ) +
				($this->repoId ? array(':repo_id' => $this->repoId) : array(':user_id' => $this->userId, ))
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
				$reportId = (int) $row['report_id'];
			}
		}

		return $reportId;
	}

	/**
	 * Converts a Markdown string to HTML
	 * 
	 * The Markdown parser is still vulnerable to XSS, so we have to filter the output, see:
	 * https://michelf.ca/blog/2010/markdown-and-xss/. The default mode seems to be to strip
	 * rather than to encode, which is fine, since there are perfectly decent ways of encoding
	 * legitimate code examples (backticks, block indent).
	 * 
	 * I've turned off caching as this by default wants to cache inside the vendor directory,
	 * ouch! If any performance issues are seen, this can probably be repointed to a more suitable
	 * location.
	 * 
	 * @param string $markdown
	 * @return string|null
	 */
	protected function convertFromMarkdown($markdown)
	{
		$html = $markdown ? \Michelf\Markdown::defaultTransform($markdown) : null;

		if ($html)
		{
			$config = HTMLPurifier_Config::createDefault();
			$config->set('Cache.DefinitionImpl', null);
			$purifier = new HTMLPurifier($config);
			try
			{
				$html = $purifier->purify($html);
			}
			catch (\Exception $e)
			{
				throw new TrivialException('Problem with parsing Markdown output');
			}
		}

		return $html;
	}
}
