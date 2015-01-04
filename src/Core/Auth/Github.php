<?php

namespace Awooga\Core\Auth;

use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriInterface;

class Github extends AuthService
{
	/**
	 * Call this to run the login process
	 * 
	 * @return boolean Success/fail
	 */
	public function execute()
	{
		// @todo Do the auth here
	}

	/**
	 * If the login failed, the reason will be given here
	 * 
	 * @return array
	 */
	public function getError()
	{
		return array();
	}

	/**
	 * Creates the authorisation service using the OAuth library
	 * 
	 * @todo Change this back to protected when we can
	 * 
	 * @param UriInterface $uri
	 * @return type
	 */
	public function getAuthService(UriInterface $uri)
	{
		// Session storage
		$storage = new Session();

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$this->getKey(),
			$this->getSecret(),
			$uri->getAbsoluteUri()
		);

		// Currently I am using a child service class that improves error handling
		$serviceFactory = new \OAuth\ServiceFactory();
		$serviceFactory->registerService('GitHubAuthService', '\\Awooga\\Core\\GitHubAuthService');
		$service = $serviceFactory->createService(
			'GitHubAuthService',
			$credentials,
			$storage,
			array('user:email', )
		);

		return $service;
	}

	/**
	 * Get the key for the chosen auth provider
	 * 
	 * This can come from server variables or from file config
	 * 
	 * @todo Don't show the login button if this is not set
	 * 
	 * @return string
	 */
	protected function getKey()
	{
		if (isset($this->config['client-id']))
		{
			return $this->config['client-id'];
		}

		$clientId = getenv('GITHUB_CLIENT_ID');
		if (!$clientId)
		{
			throw new \Exception("Cannot find auth provider client ID in server or file config");
		}

		return $clientId;
	}

	/**
	 * Get the secret for the chosen auth provider
	 * 
	 * This can come from server variables or from file config
	 * 
	 * @todo Don't show the login button if this is not set
	 * 
	 * @return string
	 */
	protected function getSecret()
	{
		if (isset($this->config['client-secret']))
		{
			return $this->config['client-secret'];
		}

		$clientSecret = getenv('GITHUB_CLIENT_SECRET');
		if (!$clientSecret)
		{
			throw new \Exception("Cannot find auth provider client secret in server or file config");
		}

		return $clientSecret;
	}
}