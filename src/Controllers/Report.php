<?php

namespace Awooga\Controllers;

class Report extends BaseController
{
	use \Awooga\Traits\Reports;

	protected $reportId;

	/**
	 * Controller for viewing a single report
	 */
	public function execute()
	{
		// Redirect if the report does not exist
		$report = $this->getReport();
		if (!$report)
		{
			// @todo Add a flash var here to offer the user a helpful error
			$url = 'http://' . $_SERVER['HTTP_HOST'] . '/browse';
			header('Location: ' . $url);
			exit();
		}

		// Get issues and URLs
		$reportIds = array((int) $report['id'], );
		$report['urls'] = $this->getRelatedUrls($reportIds);
		$report['issues'] = $this->getRelatedIssues($reportIds);

		// Assemble title (@todo need to count +unresolved+ issues, not issues in total)
		$this->setPageTitle(
			'Report "' . $report['title'] . '" containing ' . count($report['issues']) . ' issues'
		);

		echo $this->render('report', array('report' => $report, ));
	}

	public function getMenuSlug()
	{
		return 'report';
	}

	protected function getReport()
	{
		$sql = "
			SELECT * FROM report
			WHERE
				id = :report_id
				AND is_enabled = 1
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':report_id' => $this->reportId, ));

		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	public function setReportId($reportId)
	{
		$this->reportId = $reportId;
	}
}
