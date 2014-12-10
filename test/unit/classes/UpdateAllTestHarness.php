<?php

namespace Awooga\Testing;

/**
 * This class inherits from the real UpdateAll, making it more amenable to testing
 */
class UpdateAllTestHarness extends \Awooga\Core\UpdateAll
{
	protected $dateInterval = null;

	/**
	 * Public entry point for the run create method
	 */
	public function createRun()
	{
		return parent::createRun();
	}

	public function setImporter(\Awooga\Core\GitImporter $importer)
	{
		$this->importer = $importer;
	}

	/**
	 * Sets a time offset (as a DateInterval) to test due date/times
	 * 
	 * @param \DateInterval $dateInterval
	 */
	public function setTimeOffset(\DateInterval $dateInterval)
	{
		$this->dateInterval = $dateInterval;
	}

	/**
	 * Return the date/time in string format, taking into account the offset
	 */
	protected function getCurrentDateTime()
	{
		$dt = new \DateTime();
		if ($this->dateInterval)
		{
			$dt->add($this->dateInterval);
		}

		return $dt->format('Y-m-d H:i:s');
	}
}
