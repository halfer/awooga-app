<?php

namespace Awooga\Controllers;

use \League\Plates\Engine;
use \Slim\Slim;

class BaseController
{
	protected $slim;
	protected $engine;

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
}