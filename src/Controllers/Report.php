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
		$report = $this->getReportForId($this->getDriver(), $this->reportId);
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

		// Decide if this report is being viewed by its owner
		$isOwner =
			$report['user_id'] &&
			$report['user_id'] == $this->getSignedInUserId();

		echo $this->render(
			'report',
			array(
				'report' => $report, 'isOwner' => $isOwner,
			)
		);
	}

	public function getMenuSlug()
	{
		return 'report';
	}

	public function setReportId($reportId)
	{
		$this->reportId = (int) $reportId;
	}
}
