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

		// Here's the save operation
		if ($this->getSlim()->request->isPost())
		{
			$this->handleSave();
			// @todo Do redirect or display error here
		}

		echo $this->render('new-report');
	}

	/**
	 * Carries out the save operation
	 */
	protected function handleSave()
	{
		// This will fail silently if it is already created, just skeleton code for now
		// @todo Remove this
		$pdo = $this->getDriver();
		$statement = $pdo->prepare(
			"INSERT INTO user (id, last_login_at) VALUES (1, NOW())"
		);
		$statement->execute();

		// Create/update the report attached to this user
		$report = new Report(null, 1);
		$report->setDriver($pdo);
		$report->setUrl('http://example.com');
		$report->setTitle('My title');
		$report->setDescription('My description');
		$report->setIssues(array(array('issue_cat_code' => 'sql-injection',)));
		$reportId = $report->save();

		// @todo Return success or fail boolean
	}

	public function getMenuSlug()
	{
		return '/report/new';
	}
}