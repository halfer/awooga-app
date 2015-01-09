<?php

namespace Awooga\Controllers;

class Logs extends PaginatedController
{
	protected $baseTable = 'repository_log';
	protected $menuSlug = 'logs';
	protected $pageTitle = 'Import logs';

	/**
	 * Controller for logs screen
	 */
	public function execute()
	{
		echo $this->getPaginatedRender('logs', 20);
	}

	protected function getPaginatedRows($pageSize)
	{
		return $this->baseGetPaginatedRows(
			"SELECT * FROM repository_log ORDER BY id DESC",
			$pageSize
		);
	}

	/**
	 * A custom row count function that limits logs to the last few hundred
	 * 
	 * @return integer
	 */
	protected function getRowCount()
	{
		$rowCount = parent::getRowCount();
		$limit = 20 * 20;

		return $rowCount > $limit ? $limit : $rowCount;
	}
}
