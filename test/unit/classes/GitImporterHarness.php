<?php

namespace Awooga\Testing;

/**
 * This class inherits from the real GitImporter, making it more amenable to testing
 */
class GitImporterHarness extends \Awooga\GitImporter
{
	protected function runGitCommand()
	{
		// @todo Add in Git replacement in here
	}
}
