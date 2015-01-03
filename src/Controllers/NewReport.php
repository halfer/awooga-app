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
					'type_code' => '',
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

		echo $this->render(
			'new-report',
			array('report' => $report, 'errors' => $errors, )
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
				$report['issues'][$ord]['type_code'] = $code;
				$report['issues'][$ord]['description'] = '';
			}
		}

		// I add a blank code here in case we have too many descriptions
		if (isset($post['issue-description']) && is_array($post['issue-description']))
		{
			foreach ($post['issue-description'] as $ord => $description)
			{
				$report['issues'][$ord]['description'] = $description;
				if (!isset($report['issues'][$ord]['type_code']))
				{
					$report['issues'][$ord]['type_code'] = null;
				}
			}
		}

		return $report;
	}

	/**
	 * Carries out the save operation
	 */
	protected function handleSave()
	{
		// @todo This needs to be attached to the current user, not hardwired

		// Create/update the report attached to this user
		$report = new Report(null, 1);
		$report->setDriver($this->getDriver());
		$report->setUrl('http://example.com');
		$report->setTitle('My title');
		$report->setDescription('My description');
		$report->setIssues(array(array('issue_cat_code' => 'sql-injection',)));
		//$reportId = $report->save();

		return array(
			'widget' => 'The widget failed to align with the thingamajig',
		);

		// @todo Return success or fail boolean
	}

	public function getMenuSlug()
	{
		return '/report/new';
	}
}