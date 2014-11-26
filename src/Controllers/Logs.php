<?php

namespace Awooga\Controllers;

class Logs extends BaseController
{
	public function execute()
	{
		echo $this->getEngine()->render('logs');
	}
}
