<?php

namespace Awooga\Traits;

trait AuthSession
{
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