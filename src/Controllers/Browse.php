<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	protected $selectedMenu = 'browse';

	use Pagination;

	/**
	 * Controller for report browsing
	 * 
	 * @todo For each report, add in a urls array
	 */
	public function execute()
	{
		// Redirects if the page number is invalid
		$pageNumber = $this->verifyPageNumber($this->getReportCount(), 20);
		if ($pageNumber !== true)
		{
			$this->pageRedirectAndExit($pageNumber ? 'browse/' . $pageNumber : 'browse');
		}

		$limitClause = $this->getLimitClause(20);
		$sql = "
			SELECT *
			FROM report
			WHERE is_enabled = 1
			ORDER BY id DESC
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		$reports = $statement->fetchAll(\PDO::FETCH_ASSOC);
		echo $this->render('browse', array('reports' => $reports, ));
	}
}
