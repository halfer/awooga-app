<?php

namespace Awooga\Controllers;

class Repos extends BaseController
{
	use Pagination;

	/**
	 * Controller for repos screen
	 */
	public function execute()
	{
		// Redirects if the page number is invalid, fetches rows
		$repos =  $this->validatePageAndGetRows($pageSize = 20);

		// Render the reports
		echo $this->render(
			'repos',
			array(
				'repos' => $repos,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($this->getRowCount(), $pageSize),
			)
		);
	}

	protected function getPaginatedRows($pageSize)
	{
		return array();
	}

	protected function setRowCount()
	{
		$this->rowCount = 1;
	}

	protected function getMenuSlug()
	{
		return 'repos';
	}
}
