<?php

namespace Awooga\Controllers;

class Auth extends BaseController
{
	use \Awooga\Traits\AuthSession;

	protected $pageTitle = 'Login';

	/**
	 * Controller for authentication endpoint
	 */
	public function execute()
	{
		if (!$this->isAuthenticated())
		{
			// Gets provider code from query string or session
			$provider = $this->getProviderName();
			if ($provider)
			{
				$this->loginProcess($provider);
			}
			else
			{
				$this->showLoginScreen();
			}
		}
		else
		{
			$this->getSlim()->redirect('/');
		}
	}

	public function showLoginScreen()
	{
		echo $this->render(
			'login',
			array(
				'error' => false,
				// Show the requires auth message (comes from a redirect)
				'requiresAuth' => isset($_GET['require-auth']),
			)
		);
	}

	protected function loginProcess($provider)
	{
		$error = null;
		$authService = $this->getServiceProvider($provider);
		if (!$authService)
		{
			$error = 'Cannot find the requested authentication service';
		}

		if (!$error)
		{
			// Here is the login sequence
			$ok = $authService->execute();
			if ($ok)
			{
				if ($serviceUsername = $authService->getAuthenticatedName())
				{
					$this->logon($serviceUsername, $provider);
					$this->getSlim()->redirect('/');
				}
				elseif ($url = $authService->redirectTo())
				{
					$this->getSlim()->redirect($url);
				}
			}
			else
			{
				$error = $authService->getError();
			}
		}

		// Present user with login link
		echo $this->render(
			'login',
			array(
				'error' => $error,
				'requiresAuth' => false,
			)
		);
	}

	/**
	 * Gets an Awooga auth service, if one exists
	 * 
	 * @param type $provider
	 */
	protected function getServiceProvider($provider)
	{
		// An empty config is permitted, so no error need result from it not being set
		$authConfig = $this->getEnvConfig('auth-service.' . $provider, false);

		// Return null if the class does not exist
		$className = '\\Awooga\\Core\\Auth\\' . ucfirst($provider);
		if (!class_exists($className))
		{
			return null;
		}

		return new $className(
			is_array($authConfig) ? $authConfig : array(),
			$this->getSlim()->mode
		);
	}

	/**
	 * Logs on the specified username
	 * 
	 * @param string $serviceUsername
	 */
	protected function logon($serviceUsername, $provider)
	{
		// Ensure the user's login records exist
		$this->createAndFetchUserRecords($serviceUsername, $provider);

		// Prevent session fixation
		session_regenerate_id();
		
		// Store the username for cross-referencing with the database
		$_SESSION[self::SESSION_KEY_USERNAME] = $serviceUsername;
	}

	protected function createAndFetchUserRecords($serviceUsername, $provider)
	{
		// See if the username exists
		$ids = $this->getUserIdsFromUsername($this->getDriver(), $serviceUsername);
		if (is_array($ids))
		{
			// Update login time for service user record
			$this->resetLoginTime($ids['user_auth_id']);
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
		$sqlUser = "
			INSERT INTO user
				(username)
				VALUES (:username)
		";
		$pdo = $this->getDriver();
		$statement = $pdo->prepare($sqlUser);
		$statement->execute(array(':username' => $serviceUsername, ));
		$userId = $pdo->lastInsertId();

		$sqlAuth = "
			INSERT INTO user_auth
				(user_id, username, provider, last_login_at)
				VALUES (:user_id, :username, :provider, NOW())
		";
		$statementAuth = $pdo->prepare($sqlAuth);
		$statementAuth->execute(
			array(
				':user_id' => $userId,
				':username' => $serviceUsername,
				':provider' => $provider,
			)
		);
	}

	/**
	 * Reset the user service login times
	 * 
	 * @param integer $serviceId Associative array of auth and user tables to update
	 */
	protected function resetLoginTime($serviceId)
	{
		$sqlAuth = "
			UPDATE user_auth
				SET last_login_at = NOW()
			WHERE
				id = :user_auth_id
		";
		$statementAuth = $this->getDriver()->prepare($sqlAuth);
		$statementAuth->execute(array(':user_auth_id' => $serviceId));
	}

	public function getMenuSlug()
	{
		return 'auth';
	}
}