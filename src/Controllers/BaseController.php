<?php

namespace Awooga\Controllers;

use \League\Plates\Engine;
use \Slim\Slim;

abstract class BaseController
{
	protected $slim;
	protected $engine;
	protected $pdo;

	protected $selectedMenu = 'home';

	use \Awooga\Core\Database;

	public function __construct(Slim $slim, Engine $engine)
	{
		$this->slim = $slim;
		$this->engine = $engine;
	}

	/**
	 * Gets the Plates engine
	 * 
	 * @return \League\Plates\Engine
	 */
	protected function getEngine()
	{
		return $this->engine;
	}

	protected function getCounts()
	{
		return array(
			'report_count' => $this->getReportCount(),
			'issue_count' => $this->getIssueCount(),
		);
	}

	protected function getReportCount()
	{
		$sql = "
			SELECT COUNT(*) FROM report
			WHERE is_enabled = 1
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchColumn();
	}

	protected function getIssueCount()
	{
		$sql = "
			SELECT COUNT(*) FROM report r
			INNER JOIN report_issue i ON (i.report_id = r.id) 
			WHERE r.is_enabled = 1
		";
		$statement = $this->getDriver()->prepare($sql);
		$ok = $statement->execute();

		return $statement->fetchColumn();
	}

	protected function render($name, array $values = array())
	{
		$values['selectedMenu'] = $this->selectedMenu;
		$values['countData'] = $this->getCounts();

		return $this->getEngine()->render($name, $values);
	}

	final public function initAndExecute()
	{
		$this->setDriver($this->initDriver());
		$this->execute();
	}

	abstract public function execute();

	/**
	 * Creates a database connection
	 * 
	 * @todo Fix connection hardwiring
	 * 
	 * @param boolean $selectDatabase
	 * @return \PDO
	 */
	protected function initDriver($selectDatabase = true)
	{
		// Connect to the database
		$database = $selectDatabase ? 'dbname=awooga;' : '';
		$dsn = "mysql:{$database}host=localhost;username=awooga_user;password=password";
		$pdo = new \PDO($dsn, 'awooga_user', 'password');

		return $pdo;
	}
}