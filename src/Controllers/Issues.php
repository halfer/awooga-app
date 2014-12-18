<?php

namespace Awooga\Controllers;

class Issues extends PaginatedController
{
	protected $baseTable = 'issue';
	protected $menuSlug = 'issues';
	protected $pageTitle = 'Issue types';

	/**
	 * Controller for report browsing
	 */
	public function execute()
	{
		// Render the reports
		echo $this->getPaginatedRender('issues', 20);
	}

	protected function getPaginatedRows($pageSize)
	{
		$sql = "
			SELECT
				*,
				(SELECT COUNT(*)
				FROM report r
				INNER JOIN report_issue ir ON (r.id = ir.report_id)
				WHERE ir.issue_id = issue.id) report_count
			FROM issue
			ORDER BY report_count DESC
		";

		return $this->baseGetPaginatedRows($sql, $pageSize);
	}
}