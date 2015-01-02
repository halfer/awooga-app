<?php

namespace Awooga\Traits;

trait Config
{
	/**
	 * Retrieves the specified config value from environment-specific settings
	 * 
	 * @param string $mode
	 * @param string $key
	 * @param boolean $errorOnNotFound
	 * @throws \Exception
	 * @return string
	 */
	protected function getEnvConfigForMode($mode, $key, $errorOnNotFound = true)
	{
		$configs = require($this->getProjectRoot() . '/config/env-config.php');

		// If we don't have an entry for this mode, bork
		if (!array_key_exists($mode, $configs))
		{
			throw new \Exception("Configuration for mode '$mode' not found");
		}

		// If we don't have an entry for this key, bork
		if ($errorOnNotFound && !array_key_exists($key, $configs[$mode]))
		{
			throw new \Exception("Configuration key '$key' for mode '$mode' not found");
		}

		return isset($configs[$mode][$key]) ? $configs[$mode][$key] : null;
	}

	/**
	 * A root path getter, the trait client must provide this
	 */
	abstract protected function getProjectRoot();
}