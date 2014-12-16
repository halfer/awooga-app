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
		$this->setPageTitle("Import logs");

		echo $this->getPaginatedRender('logs', 20);
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
