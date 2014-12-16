<?php

namespace Awooga\Controllers;

class Issues extends BaseController
{
	use \Awooga\Traits\Pagination;

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		$this->setPageTitle("Issue types");

		// Render the reports
		echo $this->getPaginatedRender('issues', 20);
	}

	protected function setRowCount()
	{
		$this->baseSetRowCount('issue');
	}

	protected function getMenuSlug()
	{
		return 'issues';
	}

	protected function getPaginatedRows($pageSize)
	{
		$limitClause = $this->getLimitClause($pageSize);
		$sql = "
			SELECT
				*,
				(SELECT COUNT(*)
				FROM report r
				INNER JOIN report_issue ir ON (r.id = ir.report_id)
				WHERE ir.issue_id = issue.id) report_count
			FROM issue
			ORDER BY report_count DESC
			{$limitClause}
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}