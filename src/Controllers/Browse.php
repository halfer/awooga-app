<?php

namespace Awooga\Controllers;

class Browse extends BaseController
{
	protected $page;

	public function execute()
	{
		echo $this->getEngine()->render('browse');
	}

	public function setPage($page)
	{
		$this->page = (int) $page;
	}
}
