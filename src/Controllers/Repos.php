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
		// Redirects if the page number is invalid, fetches rows
		$repos = $this->validatePageAndGetRows($pageSize = 10);

		$this->setPageTitle("Source repositories");

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
		$sql = "
			SELECT COUNT(*)
			FROM repository
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();

		$this->rowCount = $statement->fetchColumn();
	}

	protected function getMenuSlug()
	{
		return 'repos';
	}
}
