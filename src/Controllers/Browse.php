<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	protected $page;

	public function execute()
	{
		$sql = "
			SELECT *
			FROM report
			WHERE is_enabled = 1
			ORDER BY id DESC
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		$reports = $statement->fetchAll(\PDO::FETCH_ASSOC);
		echo $this->getEngine()->render('browse', array('reports' => $reports, ));
	}

	public function setPage($page)
	{
		$this->page = (int) $page;
	}
}
