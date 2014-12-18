<?php

namespace Awooga\Controllers;

class About extends BaseController
{
	protected $pageTitle = 'About';

	public function execute()
	{
		echo $this->render('about');
	}

	protected function getMenuSlug()
	{
		return 'about';
	}
}
