<?php

namespace Awooga\Controllers;

class NewReport extends BaseController
{
	public function execute()
	{
		// Redirect if not signed in
		if (!$this->isAuthenticated())
		{
			$this->getSlim()->redirect('/auth?require-auth=1');
		}

		echo $this->render('new-report');
	}

	public function getMenuSlug()
	{
		return '/report/new';
	}
}