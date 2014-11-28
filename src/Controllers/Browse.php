<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	protected $selectedMenu = 'browse';

	use Pagination;

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		// Redirects if the page number is invalid
		$rowCount = $this->getReportCount();
		$pageNumber = $this->verifyPageNumber($rowCount, $pageSize = 20);
		if ($pageNumber !== true)
		{
			$this->pageRedirectAndExit($pageNumber ? 'browse/' . $pageNumber : 'browse');
		}

		$reports = $this->getReportsWithRelatedTables($pageSize);
		echo $this->render(
			'browse',
			array(
				'reports' => $reports,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($rowCount, $pageSize),
			)
		);
	}

	protected function getReportsWithRelatedTables($pageSize)
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

	protected function getRelatedUrls(array $reportIds)
	{
		$strIds = implode(',', $reportIds);
		$sql = "
			SELECT report_id, url
			FROM resource_url
			WHERE report_id IN ({$strIds})
			/* Get them in order of creation, first one is regarded as 'primary' */
			ORDER BY id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	protected function getRelatedIssues(array $reportIds)
	{
		$strIds = implode(',', $reportIds);
		$sql = "
			SELECT r.report_id, i.code issue_code
			FROM report_issue r
			INNER JOIN issue i ON (i.id = r.issue_id)
			WHERE r.report_id IN ({$strIds})
			/* Get them in order of type */
			ORDER BY r.issue_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}
