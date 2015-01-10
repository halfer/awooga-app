<?php

namespace Awooga\Controllers;

class EditReport extends NewReport
{
	use \Awooga\Traits\Reports;

	protected $reportId;

	/**
	 * When editing an old report, the initial state is retrieved from disk
	 * 
	 * @return array
	 */
	protected function getInitialReport()
	{
		return $this->getReportAndRelatedData($this->reportId);
	}

	protected function getReportAndRelatedData($id)
	{
		$report = $this->getReportForId($id);

		// We need to unwrap URL table to a string array
		$report['url'] = array();
		foreach ($this->getRelatedUrls(array($id)) as $url)
		{
			$report['urls'][] = $url['url'];
		}

		// Convert issues table to simple array
		$report['issues'] = array();
		foreach ($this->getRelatedIssues(array($id)) as $issue)
		{
			$report['issues'][] = array(
				'issue_cat_code' => $issue['code'],
				'description' => $issue['description'],
			);
		}

		return $report;
	}

	/**
	 * Sets the report ID from the front controller
	 * 
	 * @todo Can we move this, and the one in Report, to the Reports trait?
	 * 
	 * @param integer $reportId
	 */
	public function setReportId($reportId)
	{
		$this->reportId = (int) $reportId;
	}

	/**
	 * Identifies which ID we're editing
	 * 
	 * @return integer
	 */
	protected function getEditId()
	{
		return $this->reportId;
	}

	/**
	 * Decide if the report may be edited
	 * 
	 * @param integer $userId Owner of this report
	 * @return boolean
	 */
	protected function editPermitted($userId)
	{
		return $userId == $this->getSignedInUserId();
	}

	/**
	 * Let's not highlight a menu option when editing
	 * 
	 * @return string
	 */
	public function getMenuSlug()
	{
		return null;
	}
}