<?php

namespace Awooga\Core;

class DebugPDO extends \PDO
{
	protected $debugBar;

	public function setDebugBar(\DebugBar\DebugBar $debugBar)
	{
		$this->debugBar = $debugBar;
	}

	public function prepare($statement, array $driver_options = array())
	{
		if ($this->debugBar)
		{
			$this->debugBar['messages']->addMessage($statement);
		}

		return parent::prepare($statement, $driver_options);
	}
}