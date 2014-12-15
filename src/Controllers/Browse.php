<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	use \Awooga\Traits\Pagination;
	use \Awooga\Traits\Reports;

	protected $searchKeys;

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		// See if we are in search mode
		if ($this->isSearchMode())
		{
			// @todo Tidy this up, needs to be configurable
			$root = realpath(__DIR__ . '/../..') . '/filesystem/mount/search-index';
			$searcher = new \Awooga\Core\Searcher();
			$searcher->connect($root);
			$results = $searcher->search($_GET['search']);
			$this->searchKeys = array();
			foreach ($results as $row)
			{
				$this->searchKeys[] = $row->pk;
			}
		}

		// Redirects if the page number is invalid, fetches rows
		$reports = $this->validatePageAndGetRows($pageSize = 20);

		$this->setPageTitle("Browse reports");

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
	 * @todo Need to swap '*' for a specific field list
	 * 
	 * @param integer $pageSize
	 * @return array|false
	 */
	protected function getReports($pageSize)
	{
		if ($this->isSearchMode())
		{
			// @todo Need to take the right page slice out of here
			$where = implode(',', $this->searchKeys);
			$sql = "
				SELECT *
				FROM report
				WHERE is_enabled = 1
				AND id IN ($where)
				ORDER BY id DESC
			";			
		}
		else
		{
			$limitClause = $this->getLimitClause($pageSize);
			$sql = "
				SELECT *
				FROM report
				WHERE is_enabled = 1
				ORDER BY id DESC
				{$limitClause}
			";
		}
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}
