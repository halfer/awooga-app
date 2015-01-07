<?php

namespace Awooga\Controllers;

use Awooga\Core\Report;
use Awooga\Exceptions\TrivialException;

class NewReport extends BaseController
{
	/**
	 * Runs the new report controller
	 */
	public function execute()
	{
		// Redirect if not signed in
		if (!$this->isAuthenticated())
		{
			$this->getSlim()->redirect('/auth?require-auth=1');
		}

		// Create a report in an array to edit
		$report = $this->getInitialReport();

		// Redirect if we are not permitted to edit (create is always fine, edit isn't)
		if (!$this->editPermitted($report['user_id']))
		{
			// @todo Set a flash notice?
			$this->getSlim()->redirect('/report/' . $this->getEditId());
		}

		// Let's try the save operation
		$errors = array();
		if ($this->getSlim()->request->isPost())
		{
			$report = $this->getReportFromUserInput($report);
			$result = $this->handleSave($report);
			if (is_int($result))
			{
				// If the result is an int, it was successful
				$this->getSlim()->redirect('/report/' . $result . '/edit');
			}
			else
			{
				// If the result is an array, it failed
				$errors = $result;
			}
		}

		// Get the list of permitted issue types
		$issues = $this->getIssueList();

		echo $this->render(
			'edit-report',
			array(
				'report' => $report,
				'issues' => $issues,
				'errors' => $errors,
				'editId' => $this->getEditId(),
			)
		);
	}

	/**
	 * When creating a new report, the initial state is a blank report
	 * 
	 * @return array
	 */
	protected function getInitialReport()
	{
		return array(
			'urls' => array(''),
			'title' => '',
			'description' => '',
			'user_id' => null,
			'issues' => array(
				array(
					'issue_cat_code' => '',
					'description' => '',
				),
			),
		);
	}

	/**
	 * Grab the report data from user input
	 * 
	 * @param array $report
	 * @return type
	 */
	protected function getReportFromUserInput(array $report)
	{
		$post = $this->getSlim()->request->post();

		// Let's read in the report from user input
		if (isset($post['urls']) && is_array($post['urls']))
		{
			$report['urls'] = array_values($post['urls']);
		}
		if (isset($post['title']) && is_string($post['title']))
		{
			$report['title'] = $post['title'];
		}
		if (isset($post['description']) && is_string($post['description']))
		{
			$report['description'] = $post['description'];
		}

		// I add a blank description here in case there aren't enough descriptions to fill it
		if (isset($post['issue-type-code']) && is_array($post['issue-type-code']))
		{
			foreach ($post['issue-type-code'] as $ord => $code)
			{
				$report['issues'][$ord]['issue_cat_code'] = $code;
				$report['issues'][$ord]['description'] = '';
			}
		}

		// I add a blank code here in case we have too many descriptions
		if (isset($post['issue-description']) && is_array($post['issue-description']))
		{
			foreach ($post['issue-description'] as $ord => $description)
			{
				$report['issues'][$ord]['description'] = $description;
				if (!isset($report['issues'][$ord]['issue_cat_code']))
				{
					$report['issues'][$ord]['issue_cat_code'] = null;
				}
			}
		}

		return $report;
	}

	/**
	 * Carries out the save operation
	 */
	protected function handleSave(array $reportInput)
	{
		// Create/update the report attached to this user
		$report = new Report(null, $this->getSignedInUserId());
		$report->setDriver($this->getDriver());

		// These can blow up, so we wrap in catch block
		$errors = array();
		try
		{
			$report->setUrl($reportInput['urls']);
			$this->checkUrlConflict($reportInput['urls']);
			$report->setTitle($reportInput['title']);
			$report->setDescription($reportInput['description']);
			$report->setIssues($reportInput['issues']);
		}
		catch (\Exception $e)
		{
			// @todo This will only report the first error, need to fix that
			$errors[] = $e->getMessage();
		}

		if (!$errors)
		{
			try
			{
				$reportId = $report->save($this->getEditId());
			}
			catch (\Exception $e)
			{
				$errors[] = "Save failed";
			}
		}

		return $errors ? $errors : $reportId;
	}

	protected function checkUrlConflict(array $urls)
	{
		// We need to do escaping manually for an IN query
		$pdo = $this->getDriver();
		$escaped = array();
		foreach ($urls as $url)
		{
			$escaped[] = $pdo->quote($url, \PDO::PARAM_STR);
		}
		$inList = implode(',', $escaped);

		// See if any URLs are already known, for this user only
		$sql = "
			SELECT 1
			FROM resource_url u
			INNER JOIN report r ON (u.report_id = r.id)
			WHERE
				u.url IN ($inList)
				AND r.user_id = :user_id
		";
		$params = array(':user_id' => $this->getSignedInUserId(), );

		// If we are editing, allow an exception for the current report
		if ($reportId = $this->getEditId())
		{
			$sql .= " AND r.id != :report_id";
			$params[':report_id'] = $reportId;
		}

		$statement = $pdo->prepare($sql);
		$statement->execute($params);

		// If we have rows then we have a conflict
		if ($statement->rowCount())
		{
			// @todo Add singular/plural messages here
			throw new TrivialException(
				"One of these URLs is already contained within another of your reports"
			);
		}
	}

	/**
	 * Returns an array containing the issue codes
	 * 
	 * @return array
	 */
	protected function getIssueList()
	{
		$sql = "SELECT code FROM issue ORDER BY code";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();
		$issues = $statement->fetchAll(\PDO::FETCH_ASSOC);
		$out = array();
		foreach ($issues as $issue)
		{
			$out[] = $issue['code'];
		}

		return $out;
	}

	/**
	 * Identifies which ID we're editing
	 * 
	 * @return boolean|integer
	 */
	protected function getEditId()
	{
		return false;
	}

	/**
	 * Allows reports to be created
	 * 
	 * @param integer $userId Report owner (will be null when creating reports)
	 * @return boolean
	 */
	protected function editPermitted($userId)
	{
		return true;
	}

	public function getMenuSlug()
	{
		return '/report/new';
	}
}