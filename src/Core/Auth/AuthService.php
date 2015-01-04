<?php

namespace Awooga\Core\Auth;

abstract class AuthService
{
	protected $config;

	/**
	 * Set up the authorisation system with application config
	 * 
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	abstract public function execute();

	abstract public function getError();

	/**
	 * Method to determine whether this system is available
	 * 
	 * Typically this should return true if the id/secret is available
	 * 
	 * @return boolean
	 */
	public function isConfigured()
	{
		return false;
	}
}
