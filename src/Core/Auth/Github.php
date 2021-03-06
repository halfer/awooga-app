<?php

namespace Awooga\Core\Auth;

use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use Awooga\Core\GitHubAuthService;

class Github extends AuthService
{
	/**
	 * Call this to run the login process
	 * 
	 * @return boolean Success/fail
	 */
	public function execute()
	{
		$currentUri = $this->createUri();
		$service = $this->getAuthService($currentUri);
		$code = isset($_GET['code']) ? $_GET['code'] : null;

		if ($code && $this->getProviderNameFromSession())
		{
			return $this->handleProviderCallback($service, $code);
		}
		elseif ($this->getProviderNameFromQueryString())
		{
			return $this->callProvider($service);
		}
	}

	protected function callProvider(GitHubAuthService $service)
	{
		$url = $service->getAuthorizationUri();
		$state = rand(1, 9999999);
		$_SESSION['state'] = $state;
		$this->setProviderInSession('github');
		$url .= '&state=' . $state;
		$this->redirect = $url;

		return true;
	}

	protected function handleProviderCallback(GitHubAuthService $service, $code)
	{
		// This was a callback request from GitHub, get the token
		try
		{
			$service->requestAccessToken($code);
			$result = json_decode($service->request('user'), true);
		}
		catch (TokenResponseException $e)
		{
			// This seems safe to report to the user
			$this->error = $e->getMessage();
			return false;
		}

		// Clear intermediate session vars
		$this->unsetProviderInSession();

		// See if the security token matches, to ensure the request came from us
		$suppliedState = isset($_GET['state']) ? $_GET['state'] : 1;
		$savedState = isset($_SESSION['state']) ? $_SESSION['state'] : 2;
		if ($suppliedState != $savedState)
		{
			$this->error = "The login attempt appears not to have come from GitHub";
			return false;
		}
		if (isset($result['html_url']))
		{
			$this->authenticatedName = $result['html_url'];
		}

		return true;
	}

	// @todo Make use of this
	public function isConfigured()
	{
		return $this->getKey() && $this->getSecret();
	}

	/**
	 * Creates the authorisation service using the OAuth library
	 * 
	 * @param UriInterface $uri
	 * @return \Awooga\Core\GitHubAuthService
	 */
	protected function getAuthService(UriInterface $uri)
	{
		// Session storage, don't start session though - that is already done
		$storage = new Session(false);

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