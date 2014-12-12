<?php

namespace Awooga\Controllers;

class Logs extends BaseController
{
	use \Awooga\Traits\Pagination;

	/**
	 * Controller for logs screen
	 */
	public function execute()
	{
		// Redirects if the page number is invalid, fetches rows
		$logs = $this->validatePageAndGetRows($pageSize = 20);

		$this->setPageTitle("Import logs");

		echo $this->render(
			'logs',
			array(
				'logs' => $logs,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($this->getRowCount(), $pageSize),
			)
		);
	}

	protected function getPaginatedRows($pageSize)
	{
		$limitClause = $this->getLimitClause($pageSize);
		$sql = "
			SELECT *
			FROM repository_log
			ORDER BY id DESC
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);		
	}

	protected function setRowCount()
	{
		$sql = "
			SELECT COUNT(*)
			FROM repository_log
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		$this->rowCount = $statement->fetchColumn();
	}

	protected function getMenuSlug()
	{
		return 'logs';
	}
}
