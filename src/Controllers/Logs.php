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
		$sql = "
			SELECT *
			FROM repository_log
			ORDER BY id DESC
		";

		return $this->baseGetPaginatedRows($sql, $pageSize);
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
