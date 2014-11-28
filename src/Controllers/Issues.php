<?php

namespace Awooga\Controllers;

class Issues extends BaseController
{
	use Pagination;

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		// Redirects if the page number is invalid, fetches rows
		$reports = $this->validatePageAndGetRows($pageSize = 20);

		// Render the reports
		echo $this->render(
			'issues',
			array(
				'reports' => $reports,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($this->getRowCount(), $pageSize),
			)
		);
	}

	protected function setRowCount()
	{
		$this->rowCount = 1;
	}

	protected function getMenuSlug()
	{
		return 'issues';
	}

	protected function getPaginatedRows($pageSize)
	{
		return array();
	}
}