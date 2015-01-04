<?php

namespace Awooga\Core\Auth;

use OAuth\Common\Http\Uri\UriFactory;

abstract class AuthService
{
	use \Awooga\Traits\AuthSession;

	protected $config;
	protected $redirect;
	protected $error;
	protected $authenticatedName;

	/**
	 * Set up the authorisation system with application config
	 * 
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function redirectTo()
	{
		return $this->redirect;
	}

	public function getAuthenticatedName()
	{
		return $this->authenticatedName;
	}

	/**
	 * If the login failed, the reason will be given here
	 * 
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	abstract public function execute();

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

	/**
	 * Creates a URI so we can build return addresses
	 */
	protected function createUri()
	{
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');

		return $currentUri;
	}
}
