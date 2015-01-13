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
		return $this->getReportAndRelatedData($this->getDriver(), $this->reportId);
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