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
		$limitClause = $this->getLimitClause($pageSize);
		$sql = "
			SELECT
				*,
				(SELECT COUNT(*)
				FROM report r
				WHERE r.repository_id = repository.id) report_count
			FROM repository
			ORDER BY id
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
