<?php

namespace Awooga\Controllers;

use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

class Auth extends BaseController
{
	/**
	 * Controller for authentication endpoint
	 */
	public function execute()
	{
		// Session storage
		$storage = new Session();

		// No idea what this does
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$this->getKey(),
			$this->getSecret(),
			$currentUri->getAbsoluteUri()
		);

		// Instantiate the GitHub service using the credentials, http client and storage mechanism for the token
		/** @var $gitHub \OAuth\OAuth2\Service\GitHub */
		$serviceFactory = new \OAuth\ServiceFactory();
		$gitHub = $serviceFactory->createService('GitHub', $credentials, $storage, array('user:email'));

		if (!empty($_GET['code']))
		{
			// This was a callback request from github, get the token
			$gitHub->requestAccessToken($_GET['code']);

			$result = json_decode($gitHub->request('user/emails'), true);

			echo 'The first email on your github account is ' . $result[0];
			exit();
		}
		elseif (isset($_GET['go']) && $_GET['go'] == 'go')
		{
			$url = $gitHub->getAuthorizationUri();
			$url = str_replace(
				array('response_type=code&', 'type=web_server&'),
				'',
				$url
			);
			$url .= '&state=' . rand(1, 999999);
			$this->slim->redirect($url);
			exit();
		}
		else
		{
			// Present user with login link
			$url = $currentUri->getRelativeUri() . '?go=go';
			echo $this->render('login', array('url' => $url, ));
		}
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

	public function getMenuSlug()
	{
		return 'auth';
	}
}