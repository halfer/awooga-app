<?php

namespace Awooga\Testing\Unit;

/**
 * This class inherits from the real Report, making it more amenable to testing
 */
class ReportTestHarness extends \Awooga\Core\Report
{
	/**
	 * A public helper to get protected class properties
	 * 
	 * @param string $property
	 */
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
	public function getAuthorNotifiedDateAsString()
	{
		return parent::getAuthorNotifiedDateAsString();
	}

	/**
	 * Gets the protected issues property
	 * 
	 * @todo Can callers just use getProperty('issues') here?
	 * 
	 * @return array
	 */
	public function getIssues()
	{
		return $this->issues;
	}
}