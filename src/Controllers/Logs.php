<?php

namespace Awooga\Controllers;

class Logs extends BaseController
{
	public function execute()
	{
		$sql = "
			SELECT *
			FROM repository_log
			ORDER BY id DESC
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();
		$logs = $statement->fetchAll(\PDO::FETCH_ASSOC);

		echo $this->getEngine()->render('logs', array('logs' => $logs, ));
	}
}