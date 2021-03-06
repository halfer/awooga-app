<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	use \Awooga\Traits\Pagination;
	use \Awooga\Traits\Reports;

	protected $searchKeys;
	protected $pageTitle = 'Browse reports';

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		// See if we are in search mode
		$searchString = null;
		if ($this->isSearchMode())
		{
			// Get correct search index path
			$path = $this->getEnvConfig('search-index.path');

			// Set up search system
			$root = realpath(__DIR__ . '/../..') . $path;
			$searcher = new \Awooga\Core\Searcher();
			$searcher->connect($root);
			$searchString = $_GET['search'];
			$results = $searcher->search($searchString);
			$this->searchKeys = array();
			foreach ($results as $row)
			{
				$this->searchKeys[] = $row->pk;
			}
		}

		// Redirects if the page number is invalid, fetches rows
		$reports = $this->validatePageAndGetRows($pageSize = 20);

		// Render the reports
		$rowCount = $this->getRowCount();
		echo $this->render(
			'browse',
			array(
				'reports' => $reports,
				'rowCount' => $rowCount,
				'isSearch' => $this->isSearchMode(),
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($rowCount, $pageSize),
				'searchString' => $searchString,
			)
		);
	}

	protected function isSearchMode()
	{
		return isset($_GET['search']);
	}

	protected function setRowCount()
	{
		$this->rowCount = $this->isSearchMode() ?
			count($this->searchKeys) :
			$this->getReportCount();
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
			$reportsById[$issue['report_id']]['issues'][] = $issue;
		}

		return $reportsById;
	}

	/**
	 * Gets a block of reports
	 * 
	 * @param integer $pageSize
	 * @return array|false
	 */
	protected function getReports($pageSize)
	{
		if ($this->isSearchMode())
		{
			// Take a page slice out of the results array
			$slice = array_slice($this->searchKeys, ($this->getPage() - 1) * $pageSize, $pageSize);
			$where = implode(',', $slice);
			$sql = $this->getSqlToReadReports() . "
				AND report.id IN ($where)
				ORDER BY report.id DESC
			";			
		}
		else
		{
			$limitClause = $this->getLimitClause($pageSize);
			$sql = $this->getSqlToReadReports() . "
				ORDER BY report.id DESC
				{$limitClause}
			";
		}

		return $this->fetchAll($sql);
	}
}
