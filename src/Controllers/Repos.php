<?php

namespace Awooga\Controllers;

class Repos extends PaginatedController
{
	protected $baseTable = 'repository';
	protected $menuSlug = 'repos';
	protected $pageTitle = 'Source repositories';

	/**
	 * Controller for repos screen
	 */
	public function execute()
	{
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
}
