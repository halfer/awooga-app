<?php

namespace Awooga\Controllers;

class Repos extends BaseController
{
	use \Awooga\Traits\Pagination;

	/**
	 * Controller for repos screen
	 */
	public function execute()
	{
		$this->setPageTitle("Source repositories");

		// Render the reports
		echo $this->getPaginatedRender('repos', 10);
	}

	protected function getPaginatedRows($pageSize)
	{
		$sql = "
			SELECT
				*,
				(SELECT COUNT(*)
				FROM report r
				WHERE r.repository_id = repository.id) report_count
			FROM repository
			ORDER BY id
		";

		return $this->baseGetPaginatedRows($sql, $pageSize);
	}

	protected function setRowCount()
	{
		$this->baseSetRowCount('repository');
	}

	protected function getMenuSlug()
	{
		return 'repos';
	}
}
