<?php

namespace Awooga\Controllers;

use Awooga\Core\Report;

class NewReport extends BaseController
{
	public function execute()
	{
		// Redirect if not signed in
		if (!$this->isAuthenticated())
		{
			$this->getSlim()->redirect('/auth?require-auth=1');
		}

		// Create a report in an array to edit
		$report = array(
			'urls' => array(''),
			'title' => '',
			'description' => '',
			'issues' => array(
				array(
					'issue_cat_code' => '',
					'description' => '',
				),
			),
		);

		// Let's try the save operation
		$errors = array();
		if ($this->getSlim()->request->isPost())
		{
			$report = $this->getReportFromUserInput($report);
			$result = $this->handleSave($report);
			if (is_int($result))
			{
				// If the result is an int, it was successful
				$this->getSlim()->redirect('/report/' . $result);
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
			'new-report',
			array('report' => $report, 'issues' => $issues, 'errors' => $errors, )
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
		$report = new Report(null, $this->getUserId());
		$report->setDriver($this->getDriver());

		// These can blow up, so we wrap in catch block
		$errors = array();
		try
		{
			$report->setUrl($reportInput['urls']);
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
				$reportId = $report->save();
			}
			catch (\Exception $e)
			{
				$errors[] = "Save failed";
			}
		}

		return $errors ? $errors : $reportId;
	}

	protected function getUserId()
	{
		$sql = "SELECT id FROM user WHERE username = :username";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':username' => $this->getSignedInUsername()));

		return $statement->fetchColumn();
	}

	protected function getIssueList()
	{
		$sql = "SELECT code FROM issue ORDER BY description";
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

	public function getMenuSlug()
	{
		return '/report/new';
	}
}