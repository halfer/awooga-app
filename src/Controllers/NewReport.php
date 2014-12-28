<?php

namespace Awooga\Controllers;

class NewReport extends BaseController
{
	public function execute()
	{
		echo $this->render('new-report');
	}

	public function getMenuSlug()
	{
		return '/new/report';
	}
}