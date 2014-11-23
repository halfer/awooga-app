<?php

namespace Awooga\Testing;

/**
 * This class inherits from the real UpdateAll, making it more amenable to testing
 */
class UpdateAllTestHarness extends \Awooga\UpdateAll
{
	/**
	 * Public entry point for the run create method
	 */
	public function createRun()
	{
		return parent::createRun();
	}

	public function setImporter(\Awooga\GitImporter $importer)
	{
		$this->importer = $importer;
	}
}
