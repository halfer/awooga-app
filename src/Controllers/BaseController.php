<?php

namespace Awooga\Controllers;

use \League\Plates\Engine;
use \Slim\Slim;
use \DebugBar\StandardDebugBar;

abstract class BaseController
{
	const SESSION_KEY_USERNAME = 'username';

	protected $slim;
	protected $engine;
	protected $pageTitle;

	use \Awooga\Traits\Database;
	use \Awooga\Traits\Runner;
	use \Awooga\Traits\Config;

	public function __construct(Slim $slim, Engine $engine)
	{
		$this->slim = $slim;
		$this->engine = $engine;
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
		$statement->execute();

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
		$statement->execute();

		return $statement->fetchColumn();
	}

	/**
	 * Shortcut rendering method that sets up a few template values
	 * 
	 * @param string $name
	 */
	protected function render($name, array $values = array())
	{
		$values['selectedMenu'] = $this->getMenuSlug();
		$values['countData'] = $this->getCounts();

		// Remove any protocol from the username
		$values['username'] = preg_replace('#^https?://#', '', $this->getSignedInUsername());

		// Inject static title if one is set
		if ($this->pageTitle)
		{
			$this->setPageTitle($this->pageTitle);
		}

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
	 * Sets up the page title
	 * 
	 * @param string $title
	 */
	protected function setPageTitle($title)
	{
		$this->engine->addData(array('title' => $title, ));
	}

	/**
	 * Creates a database connection
	 * 
	 * @param boolean $selectDatabase
	 * @return \PDO
	 */
	protected function initDriver($selectDatabase = true)
	{
		// Get database settings
		$config = $this->getEnvConfig('database');

		// Connect to the database
		$database = $selectDatabase ? "dbname={$config['database']};" : '';
		$dsn = "mysql:{$database}host={$config['host']};username={$config['username']};password={$config['password']}";
		$pdo = new \PDO($dsn, $config['username'], $config['password']);

		// Add debugging facility if appropriate
		if ($this->useDebugBar())
		{
			$traceablePDO = new \DebugBar\DataCollector\PDO\TraceablePDO($pdo);
			$this->getDebugBar()->addCollector(
				new \DebugBar\DataCollector\PDO\PDOCollector($traceablePDO)
			);

			// Report connection string
			$this->debugMessage("Connection string: $dsn");
		}

		return $pdo;
	}

	/**
	 * Returns whether a user is logged on to the current session
	 * 
	 * @return boolean
	 */
	protected function isAuthenticated()
	{
		return (boolean) $this->getSignedInUsername();
	}

	protected function getSignedInUsername()
	{
		$key = self::SESSION_KEY_USERNAME;

		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	/**
	 * Reads the username from the session and retrieves a user ID
	 * 
	 * @return integer
	 */
	protected function getSignedInUserId()
	{
		return $this->fetchColumn(
			$this->getDriver(),
			"SELECT id FROM user WHERE username = :username",
			array(':username' => $this->getSignedInUsername())
		);
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
		if ($this->useDebugBar())
		{
			$debugbar = new StandardDebugBar();
			$jsRenderer = $debugbar->getJavascriptRenderer('/assets/debugbar');
			$this->slim->debugbar = $debugbar;

			// Report the mode
			$this->debugMessage("Environment: " . $this->slim->mode);
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

	protected function debugMessage($message)
	{
		$this->getDebugBar()['messages']->addMessage($message);
	}

	protected function useDebugBar()
	{
		return !$this->isProduction() && !$this->isTest();
	}

	protected function isProduction()
	{
		return $this->slim->mode == 'production';
	}

	protected function isTest()
	{
		return $this->slim->mode == 'test';		
	}

	/**
	 * Retrieves the specified config value from environment-specific settings
	 * 
	 * @param string $key
	 * @param boolean $errorOnNotFound
	 * @return string
	 */
	protected function getEnvConfig($key, $errorOnNotFound = true)
	{
		return $this->getEnvConfigForMode($this->slim->mode, $key, $errorOnNotFound);
	}

	/**
	 * Gets the app singleton
	 * 
	 * @return Slim
	 */
	protected function getSlim()
	{
		return $this->slim;
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

	/**
	 * Returns the root path of the project
	 * 
	 * @return string
	 */
	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/../..');
	}
}
