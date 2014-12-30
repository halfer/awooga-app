<?php

namespace Awooga\Controllers;

class Logout extends BaseController
{
	public function execute()
	{
		// Sign out the current user, then redirect
		$_SESSION[self::SESSION_KEY_USERNAME] = null;
		$this->getSlim()->redirect('/');
	}

	public function getMenuSlug()
	{
		return null;
	}
}