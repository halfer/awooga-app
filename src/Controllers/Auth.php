<?php

namespace Awooga\Controllers;

use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriInterface;

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

		if ($this->getProviderName() == 'github')
		{
			// Just using GitHub at the moment
			$service = $this->getAuthService($currentUri);
			$code = isset($_GET['code']) ? $_GET['code'] : null;

			if ($code)
			{
				// This was a callback request from github, get the token
				$service->requestAccessToken($code);
				$result = json_decode($service->request('user'), true);

				// Clear intermediate session vars
				unset($_SESSION['provider']);

				if (isset($result['html_url']))
				{
					$this->logon($result['html_url']);
					$this->getSlim()->redirect('/');
				}
			}
			else
			{
				$url = $service->getAuthorizationUri();
				$_SESSION['provider'] = 'github';
				$url .= '&state=' . rand(1, 999999);
				$this->slim->redirect($url);
			}
		}

		// Present user with login link
		echo $this->render('login');
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

		$serviceFactory = new \OAuth\ServiceFactory();
		$service = $serviceFactory->createService('GitHub', $credentials, $storage, array('user:email'));

		return $service;
	}

	/**
	 * Get the key for the chosen auth provider
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
	 * @return string
	 */
	protected function getSecret()
	{
		return getenv('GITHUB_CLIENT_SECRET');		
	}

	protected function getProviderName()
	{
		$provider = isset($_GET['provider']) ? $_GET['provider'] : null;
		if (!$provider)
		{
			$provider = isset($_SESSION['provider']) ? $_SESSION['provider'] : null;
		}

		return $provider;
	}

	protected function logon($username)
	{
		$_SESSION['username'] = $username;
	}

	public function getMenuSlug()
	{
		return 'auth';
	}
}