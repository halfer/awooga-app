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

				// See if the security token matches, to ensure the request came from us
				$suppliedState = isset($_GET['state']) ? $_GET['state'] : 1;
				$savedState = isset($_SESSION['state']) ? $_SESSION['state'] : 2;
				if ($suppliedState != $savedState)
				{
					$error = "The login attempt appears not to have come from GitHub";
				}
				elseif (isset($result['html_url']))
				{
					$this->logon($result['html_url']);
					$this->getSlim()->redirect('/');
				}
			}
			elseif ($this->getProviderNameFromQueryString())
			{
				$url = $service->getAuthorizationUri();
				$state = rand(1, 9999999);
				$_SESSION['state'] = $state;
				$_SESSION['provider'] = 'github';
				$url .= '&state=' . $state;
				$this->slim->redirect($url);
			}
		}

		// Present user with login link
		echo $this->render(
			'login',
			array(
				'error' => $error,
				// Show the requires auth message (comes from a redirect)
				'requiresAuth' => isset($_GET['require-auth']),
			)
		);
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

	/**
	 * Logs on the specified username
	 * 
	 * @param string $serviceUsername
	 */
	protected function logon($serviceUsername)
	{
		// @todo Remove hardwiring for provider name
		$this->createAndFetchUserRecords($serviceUsername, 'github');

		// Prevent session fixation
		session_regenerate_id();

		// Store the username for cross-referencing with the database
		$_SESSION[self::SESSION_KEY_USERNAME] = $serviceUsername;
	}

	protected function createAndFetchUserRecords($serviceUsername, $provider)
	{
		// See if the username exists
		$sql = "
			SELECT
				ua.id user_auth_id,
				u.id user_id
			FROM user_auth ua
			INNER JOIN user u ON (ua.user_id = u.id)
			WHERE
				ua.username = :username
		";
		$statement = $this->getDriver()->prepare($sql);
		$statement->execute(array(':username' => $serviceUsername, ));

		if ($statement->rowCount() === 1)
		{
			// Update login time for both user records
			$this->resetLoginTime($serviceUsername, $provider);
		}
		else
		{
			// Create two user records
			$this->createUserRecords($serviceUsername, $provider);
		}
	}

	/**
	 * Creates user records if this is a new user
	 * 
	 * The user table provides a general identity, and can have connected to it any
	 * number of user_auth records. At the time of writing only GitHub is supported but
	 * this can easily change if there is demand.
	 * 
	 * @todo Wrap this in a transaction and roll back if there is a problem
	 * 
	 * @param string $serviceUsername
	 * @param string $provider
	 */
	protected function createUserRecords($serviceUsername, $provider)
	{
		// We use the service username (e.g. https://github.com/fred) but this will be
		// renamable in the future
		$sqlAuth = "
			INSERT INTO user
				(username, last_login_at)
				VALUES (:username, NOW())
		";
		$pdo = $this->getDriver();
		$statement = $pdo->prepare($sqlAuth);
		$statement->execute(array(':username' => $serviceUsername, ));
		$userId = $pdo->lastInsertId();

		$sqlService = "
			INSERT INTO user_auth
				(user_id, username, provider, last_login_at)
				VALUES (:user_id, :username, :provider, NOW())
		";
		$statementAuth = $pdo->prepare($sqlService);
		$statementAuth->execute(
			array(
				':user_id' => $userId,
				':username' => $serviceUsername,
				':provider' => $provider,
			)
		);
	}

	/**
	 * Reset the user and user service login times
	 * 
	 * @param string $serviceUsername
	 * @param string $provider
	 */
	protected function resetLoginTime($serviceUsername, $provider)
	{
		// @todo Add the body of this method
	}

	public function getMenuSlug()
	{
		return 'auth';
	}
}