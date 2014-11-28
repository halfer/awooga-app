<?php

namespace Awooga\Controllers;

class Logs extends BaseController
{
	protected $selectedMenu = 'logs';

	use Pagination;

	/**
	 * Controller for logs screen
	 */
	public function execute()
	{
		// Redirects if the page number is invalid
		$rowCount = $this->getRowCount();
		$pageNumber = $this->verifyPageNumber($rowCount, $pageSize = 30);
		if ($pageNumber !== true)
		{
			$this->pageRedirectAndExit($pageNumber ? 'logs/' . $pageNumber : 'logs');
		}

		$limitClause = $this->getLimitClause($pageSize);
		$sql = "
			SELECT *
			FROM repository_log
			ORDER BY id DESC
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		$logs = $statement->fetchAll(\PDO::FETCH_ASSOC);

		echo $this->render(
			'logs',
			array(
				'logs' => $logs,
				'currentPage' => $this->getPage(),
				'maxPage' => $this->getMaxPage($rowCount, $pageSize),
			)
		);
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
}
