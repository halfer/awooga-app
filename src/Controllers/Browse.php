<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	protected $selectedMenu = 'browse';

	use Pagination;

	/**
	 * Controller for report browsing
	 * 
	 * @todo For each report, add in a urls array
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

		$reports = $this->getReports($pageSize);
		echo $this->render(
			'browse',
			array(
				'reports' => $reports,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($rowCount, $pageSize),
			)
		);
	}

	protected function getReports($pageSize)
	{
		$limitClause = $this->getLimitClause($pageSize);
		$sqlReports = "
			SELECT *
			FROM report
			WHERE is_enabled = 1
			ORDER BY id DESC
			{$limitClause}
		";
		$statementReports = $this->getDriver()->prepare($sqlReports);
		$ok1 = $statementReports->execute();
		$reports = $statementReports->fetchAll(\PDO::FETCH_ASSOC);

		$reportIds = array();
		// Convert the reports to an pk-indexed array
		$reportsById = array();
		foreach ($reports as $report)
		{
			$id = $report['id'];
			$reportsById[$id] = $report;
			$reportsById[$id]['urls'] = array();

			// Let's also get a list of report PKs
			$reportIds[] = $id;
		}

		// Get the related links
		$strIds = implode(',', $reportIds);
		$sqlUrls = "
			SELECT *
			FROM resource_url
			WHERE report_id IN ({$strIds})
			/* Get them in order of creation, first one is regarded as 'primary' */
			ORDER BY id
		";
		$statementLinks = $this->getDriver()->prepare($sqlUrls);
		$ok2 = $statementLinks->execute();
		$urls = $statementLinks->fetchAll(\PDO::FETCH_ASSOC);

		// Add each link to the right report
		foreach($urls as $url)
		{
			$reportsById[$url['report_id']]['urls'][] = $url['url'];
		}

		return $reportsById;
	}
}
