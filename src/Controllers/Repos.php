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
		// Redirects if the page number is invalid
		$rowCount =  $this->checkPageOrRedirect($pageSize = 20);

		echo $this->render('repos');
	}

	protected function getPaginatedRows($pageSize)
	{
		return array();
	}

	protected function getRowCount()
	{
		return 1;
	}

	protected function getMenuSlug()
	{
		return 'repos';
	}
}
