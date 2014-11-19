<?php

namespace Awooga\Testing;

class ReportTestHarness extends \Awooga\Report
{
	public function getProperty($property)
	{
		return $this->$property;
	}

	/**
	 * Special rules for URLs - if there's just one we return it as a string
	 * 
	 * @return mixed
	 */
	public function getUrl()
	{
		if (count($this->urls) == 1)
		{
			return $this->urls[0];
		}
		else
		{
			return $this->urls;
		}
	}

	/**
	 * Makes a protected method public
	 * 
	 * @return string
	 */
	public function getAuthorNotifiedDateAsStringPublic()
	{
		return $this->getAuthorNotifiedDateAsString();
	}
}