<?php

namespace Awooga\Traits;

trait Reports
{
	/**
	 * Shared block of SQL to retrieve report(s)
	 * 
	 * @todo Need to swap 'report.*' for a specific field list
	 * 
	 * @return string
	 */
	protected function getSqlToReadReports()
	{
		return "
			SELECT
				report.*,
				user.username
			FROM report
			LEFT JOIN user ON (report.user_id = user.id)
			WHERE
				report.is_enabled = 1
		";
	}

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
				r.description,
				r.resolved_at
			FROM report_issue r
			INNER JOIN issue i ON (i.id = r.issue_id)
			WHERE r.report_id IN ({$strIds})
			/* Get them in order of type */
			ORDER BY r.issue_id
		";

		return $this->fetchAll($sql);
	}

	protected function getReportForId(\PDO $pdo, $id)
	{
		$sql = $this->getSqlToReadReports() . " AND report.id = :report_id";
		$statement = $pdo->prepare($sql);
		$ok = $statement->execute(array(':report_id' => $id, ));

		if (!$ok)
		{
			throw new \Exception('Could not fetch report');
		}

		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	protected function getReportAndRelatedData(\PDO $pdo, $id)
	{
		$report = $this->getReportForId($pdo, $id);

		// We need to unwrap URL table to a string array
		$report['urls'] = array();
		foreach ($this->getRelatedUrls(array($id)) as $url)
		{
			$report['urls'][] = $url['url'];
		}

		// Convert issues table to simple array
		$report['issues'] = array();
		foreach ($this->getRelatedIssues(array($id)) as $issue)
		{
			$report['issues'][] = array(
				'issue_cat_code' => $issue['code'],
				'description' => $issue['description'],
				'resolved_at' => $issue['resolved_at'],
			);
		}

		return $report;
	}

	/**
	 * This fetch method must be provided by the trait client
	 * 
	 * @param string $sql
	 * @return array
	 */
	abstract protected function fetchAll($sql);
}
