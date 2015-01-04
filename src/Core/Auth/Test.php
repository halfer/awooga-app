<?php

namespace Awooga\Core\Auth;

class Test extends AuthService
{
	/**
	 * A simple login system for the test environment
	 * 
	 * @return boolean
	 */
	public function execute()
	{
		if ($this->environment == 'test')
		{
			$this->authenticatedName = 'testuser';
			return true;
		}

		$this->error = "This authorisation provider is not enabled";
		return false;
	}
}
