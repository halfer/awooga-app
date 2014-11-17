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
		$this->isRequired($url);

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
	 * @todo Does the array type-hint throw a catchable error?
	 * 
	 * @param array $issues
	 */
	public function setIssues(array $issues)
	{
		$this->isRequired($issues);

		$this->issues = $issues;
	}

	/**
	 * Sets an optional author notified date
	 * 
	 * @param string $notifiedDate
	 */
	public function setAuthorNotifiedDate($notifiedDate)
	{
		$this->notifiedDate = date_parse($notifiedDate);
	}

	public function update()
	{
		// See if we are editing a report or creating a new one
		if ($reportId = $this->getCurrentReport())
		{
			
		}
		else
		{
			
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
			throw new Exception("This field is expected to be a string");			
		}
	}

	protected function isRequired($data)
	{
		if (!$data)
		{
			throw new Exception("This field is required");
		}		
	}
}