<?php

namespace Awooga\Traits;

trait Reports
{
	protected function getRelatedUrls(array $reportIds)
	{
		$strIds = implode(',', $reportIds);
		$sql = "
			SELECT report_id, url
			FROM resource_url
			WHERE report_id IN ({$strIds})
			/* Get them in order of creation, first one is regarded as 'primary' */
			ORDER BY id
		";

		return $this->fetchAll($sql);
	}

	protected function getRelatedIssues(array $reportIds)
	{
		$strIds = implode(',', $reportIds);
		$sql = "
			SELECT
				r.report_id,
				i.code,
				r.description_html,
				r.resolved_at
			FROM report_issue r
			INNER JOIN issue i ON (i.id = r.issue_id)
			WHERE r.report_id IN ({$strIds})
			/* Get them in order of type */
			ORDER BY r.issue_id
		";

		return $this->fetchAll($sql);
	}

	/**
	 * The database driver must be provided by the trait client
	 * 
	 * @return \PDO
	 */
	abstract protected function getDriver();

	/**
	 * This fetch method must be provided by the trait client
	 * 
	 * @param string $sql
	 * @return array
	 */
	abstract protected function fetchAll($sql);
}
