<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	use \Awooga\Traits\Pagination;
	use \Awooga\Traits\Reports;

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		// Redirects if the page number is invalid, fetches rows
		$reports = $this->validatePageAndGetRows($pageSize = 20);

		// Render the reports
		echo $this->render(
			'browse',
			array(
				'reports' => $reports,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($this->getRowCount(), $pageSize),
			)
		);
	}

	protected function setRowCount()
	{
		$this->rowCount = $this->getReportCount();
	}

	protected function getMenuSlug()
	{
		return 'browse';
	}

	protected function getPaginatedRows($pageSize)
	{
		$reports = $this->getReports($pageSize);

		// Convert the reports to an pk-indexed array
		$reportIds = array();
		$reportsById = array();
		foreach ($reports as $report)
		{
			$id = $report['id'];
			$reportsById[$id] = $report;

			// Add in an empty field for URLs and issues
			$reportsById[$id]['urls'] = array();
			$reportsById[$id]['issues'] = array();

			// Let's also get a list of report PKs
			$reportIds[] = $id;
		}

		// Add each link to the right report
		foreach($this->getRelatedUrls($reportIds) as $url)
		{
			$reportsById[$url['report_id']]['urls'][] = $url['url'];
		}

		// Add each issue to the right report
		foreach($this->getRelatedIssues($reportIds) as $issue)
		{
			$reportsById[$issue['report_id']]['issues'][] = $issue['issue_code'];
		}

		return $reportsById;
	}

	/**
	 * Gets a block of reports
	 * 
	 * @todo Need to swap '*' for a specific field list
	 * 
	 * @param integer $pageSize
	 * @return array|false
	 */
	protected function getReports($pageSize)
	{
		$limitClause = $this->getLimitClause($pageSize);
		$sql = "
			SELECT *
			FROM report
			WHERE is_enabled = 1
			ORDER BY id DESC
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}
