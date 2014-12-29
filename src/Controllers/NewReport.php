<?php

namespace Awooga\Controllers;

class NewReport extends BaseController
{
	public function execute()
	{
		// @todo Redirect if not signed in
		echo $this->render('new-report');
	}

	public function getMenuSlug()
	{
		return '/report/new';
	}
}