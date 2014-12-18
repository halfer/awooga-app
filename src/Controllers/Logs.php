<?php

namespace Awooga\Controllers;

class Logs extends PaginatedController
{
	protected $baseTable = 'repository_log';
	protected $menuSlug = 'logs';

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
		return $this->baseGetPaginatedRows(
			"SELECT * FROM repository_log ORDER BY id DESC",
			$pageSize
		);
	}
}
