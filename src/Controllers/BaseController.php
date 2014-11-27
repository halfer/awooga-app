<?php

namespace Awooga\Controllers;

use \League\Plates\Engine;
use \Slim\Slim;

class BaseController
{
	protected $slim;
	protected $engine;

	protected $selectedMenu = 'home';

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

	protected function render($name, array $values = array())
	{
		$values['selectedMenu'] = $this->selectedMenu;

		return $this->getEngine()->render($name, $values);
	}

	protected function getDriver($selectDatabase = true)
	{
		// Connect to the database
		$database = $selectDatabase ? 'dbname=awooga;' : '';
		$dsn = "mysql:{$database}host=localhost;username=awooga_user;password=password";
		$pdo = new \PDO($dsn, 'awooga_user', 'password');

		return $pdo;
	}
}