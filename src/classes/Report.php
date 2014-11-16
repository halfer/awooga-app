<?php

namespace Awooga;

class Report
{
	public function __construct($repoId)
	{
		
	}

	public function setTitle($title)
	{
		$this->isRequired($title);
		$this->isString($title);
	}

	public function setUrl($url)
	{
		$this->isRequired($url);
		// @todo Allow string or array
	}

	public function setDescription($description)
	{
		$this->isRequired($description);
		$this->isString($description);
	}

	/**
	 * Setter to accept the issue array
	 * 
	 * @todo Does the array type-hint throw a catchable error?
	 * 
	 * @param array $issues
	 */
	public function setIssues(array $issues)
	{
		$this->isRequired($issues);		
	}

	public function setAuthorNotifiedDate($notifiedDate)
	{
		
	}

	public function update()
	{
		
	}

	protected function isString($string)
	{
		if (!is_string($string))
		{
			throw new Exception("This field is expected to be a string");			
		}
	}

	protected function isRequired($data)
	{
		if (!$data)
		{
			throw new Exception("This field is required");
		}		
	}
}