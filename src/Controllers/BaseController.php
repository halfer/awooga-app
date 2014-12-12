<?php

namespace Awooga\Controllers;

use \League\Plates\Engine;
use \Slim\Slim;
use \DebugBar\StandardDebugBar;

abstract class BaseController
{
	protected $slim;
	protected $engine;
	protected $pdo;

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
		$values['selectedMenu'] = $this->getMenuSlug();
		$values['countData'] = $this->getCounts();

		return $this->getEngine()->render($name, $values);
	}

	abstract protected function getMenuSlug();

	final public function initAndExecute()
	{
		$this->configureDebugging();
		$this->initDebugBar();
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

		// Add debugging facility if appropriate
		if (!$this->isProduction())
		{
			$traceablePDO = new \DebugBar\DataCollector\PDO\TraceablePDO($pdo);
			$this->getDebugBar()->addCollector(
				new \DebugBar\DataCollector\PDO\PDOCollector($traceablePDO)
			);
		}

		return $pdo;
	}

	/**
	 * Turns off debug mode for production env
	 */
	protected function configureDebugging()
	{
		$app = $this->slim;
		$app->configureMode('production', function () use ($app)
		{
			$app->config(array('debug' => false, ));
		});
	}

	protected function initDebugBar()
	{
		// Set up the debug bar, but not in production
		$jsRenderer = null;
		if (!$this->isProduction())
		{
			$debugbar = new StandardDebugBar();
			$jsRenderer = $debugbar->getJavascriptRenderer('/assets/debugbar');
			$this->slim->debugbar = $debugbar;
		}
		$this->engine->addData(
			array('debugbarRenderer' => $jsRenderer, )
		);
	}

	protected function getDebugBar()
	{
		if (!isset($this->slim->debugbar) || !$this->slim->debugbar)
		{
			throw new \Exception('Debug bar not set');
		}

		return $this->slim->debugbar;
	}

	protected function isProduction()
	{
		return $this->slim->mode == 'production';
	}
}
