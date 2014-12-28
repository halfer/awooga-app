<?php

namespace Awooga\Controllers;

use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Http\Exception\TokenResponseException;

class Auth extends BaseController
{
	/**
	 * Controller for authentication endpoint
	 */
	public function execute()
	{
		if (!$this->isAuthenticated())
		{
			$this->loginProcess();
		}
		else
		{
			$this->getSlim()->redirect('/');
		}
	}

	protected function loginProcess()
	{
		// Supply a URI so we can build return addresses
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');

		$error = null;
		if ($this->getProviderName() == 'github')
		{
			// Just using GitHub at the moment
			$service = $this->getAuthService($currentUri);
			$code = isset($_GET['code']) ? $_GET['code'] : null;

			if ($code && $this->getProviderNameFromSession())
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
					$error = $e->getMessage();
					$result = array();
				}

				// Clear intermediate session vars
				unset($_SESSION['provider']);

				if (isset($result['html_url']))
				{
					$this->logon($result['html_url']);
					$this->getSlim()->redirect('/');
				}
			}
			elseif ($this->getProviderNameFromQueryString())
			{
				$url = $service->getAuthorizationUri();
				$_SESSION['provider'] = 'github';
				$url .= '&state=' . rand(1, 999999);
				$this->slim->redirect($url);
			}
		}

		// Present user with login link
		echo $this->render('login', array('error' => $error, ));
	}

	protected function getAuthService(UriInterface $uri)
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
	 * @todo Don't show the login button if this is not set
	 * 
	 * @return string
	 */
	protected function getKey()
	{
		return getenv('GITHUB_CLIENT_ID');
	}

	/**
	 * Get the secret for the chosen auth provider
	 * 
	 * @todo Don't show the login button if this is not set
	 * 
	 * @return string
	 */
	protected function getSecret()
	{
		return getenv('GITHUB_CLIENT_SECRET');
	}

	protected function getProviderName()
	{
		$provider = $this->getProviderNameFromQueryString();
		if (!$provider)
		{
			$provider = $this->getProviderNameFromSession();
		}

		return $provider;
	}

	protected function getProviderNameFromQueryString()
	{
		return isset($_GET['provider']) ? $_GET['provider'] : null;
	}

	protected function getProviderNameFromSession()
	{
		return isset($_SESSION['provider']) ? $_SESSION['provider'] : null;		
	}

	public function getMenuSlug()
	{
		return 'auth';
	}
}