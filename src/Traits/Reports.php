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
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	protected function getRelatedIssues(array $reportIds)
	{
		$strIds = implode(',', $reportIds);
		$sql = "
			SELECT
				r.report_id,
				i.code issue_code,
				r.description_html
			FROM report_issue r
			INNER JOIN issue i ON (i.id = r.issue_id)
			WHERE r.report_id IN ({$strIds})
			/* Get them in order of type */
			ORDER BY r.issue_id
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
}
