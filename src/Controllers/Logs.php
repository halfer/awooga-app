<?php

namespace Awooga\Controllers;

class Logs extends BaseController
{
	use Pagination;

	/**
	 * Controller for logs screen
	 */
	public function execute()
	{
		// Redirects if the page number is invalid
		$rowCount = $this->checkPageOrRedirect($pageSize = 30);
		$logs = $this->getPaginatedRows($pageSize);

		echo $this->render(
			'logs',
			array(
				'logs' => $logs,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($rowCount, $pageSize),
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

	protected function getRowCount()
	{
		$sql = "
			SELECT *
			FROM repository_log
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchColumn();
	}

	protected function getMenuSlug()
	{
		return 'logs';
	}
}
