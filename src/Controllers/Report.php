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

		echo $this->render(
			'report',
			array(
				'report' => $report,
				'isOwner' => $report['user_id'] == $this->getSignedInUserId(),
			)
		);
	}

	public function getMenuSlug()
	{
		return 'report';
	}

	protected function getReport()
	{
		$sql = $this->getSqlToReadReports() . " AND report.id = :report_id";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute(array(':report_id' => $this->reportId, ));

		if (!$ok)
		{
			throw new \Exception('Could not fetch report');
		}

		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	public function setReportId($reportId)
	{
		$this->reportId = (int) $reportId;
	}
}
