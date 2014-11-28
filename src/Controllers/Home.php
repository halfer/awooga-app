<?php

namespace Awooga\Controllers;

class Home extends BaseController
{
	public function execute()
	{
		echo $this->render('home');
	}

	protected function getMenuSlug()
	{
		return 'home';
	}
}
