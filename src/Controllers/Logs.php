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
		$statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);		
	}

	protected function setRowCount()
	{
		$this->baseSetRowCount('repository_log');
	}

	protected function getMenuSlug()
	{
		return 'logs';
	}
}
