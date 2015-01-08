<?php

namespace Awooga\Traits;

trait AuthSession
{
	/**
	 * Returns the user and service user IDs given a service username
	 * 
	 * @param \PDO $pdo
	 * @param string $serviceUsername
	 * @return array|boolean
	 */
	protected function getUserIdsFromUsername(\PDO $pdo, $serviceUsername)
	{
		$sql = "
			SELECT
				ua.id user_auth_id,
				u.id user_id
			FROM user_auth ua
			INNER JOIN user u ON (ua.user_id = u.id)
			WHERE
				ua.username = :username
		";
		$statement = $pdo->prepare($sql);
		$statement->execute(array(':username' => $serviceUsername, ));

		return $statement->rowCount() === 1 ? $statement->fetch(\PDO::FETCH_ASSOC) : false;
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

	protected function setProviderInSession($provider)
	{
		$_SESSION['provider'] = $provider;
	}

	protected function unsetProviderInSession()
	{
		unset($_SESSION['provider']);
	}
}