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
		$pageNumber = $this->verifyPageNumber($this->getRowCount(), 30);
		if ($pageNumber !== true)
		{
			$this->pageRedirectAndExit($pageNumber ? 'logs/' . $pageNumber : 'logs');
		}

		$limitClause = $this->getLimitClause(30);
		$sql = "
			SELECT *
			FROM repository_log
			ORDER BY id DESC
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		$logs = $statement->fetchAll(\PDO::FETCH_ASSOC);

		echo $this->render('logs', array('logs' => $logs, ));
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
