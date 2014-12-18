<?php

// Autoload for app-level classes, traits and interfaces

namespace Awooga;

class Autoloader
{
	const PREFIX = 'Awooga';

	public function mainLoader($class)
	{
		$loaded = false;
		$slashes = str_replace('\\', '/', $class);
		$path =
			$this->getProjectRoot() . '/src/' .
			str_replace(self::PREFIX . '/', '', $slashes) . '.php';
		if (file_exists($path))
		{
			require_once $path;
			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * A loader for test classes
	 */
	public function testLoader($class)
	{
		// Here's the mapping of prefixes to possible paths
		$namespaces = array(
			'Awooga\Testing\Browser' => array('test/browser/classes', 'test/browser/tests', ),
			'Awooga\Testing' => array('test/traits', ),
			'Awooga\Testing\Unit' => array('test/unit/classes', 'test/unit/tests', ),
		);

		$loaded = false;
		foreach ($namespaces as $namespace => $searchPaths)
		{
			// If we find a namespace match...
			if (strpos($class, $namespace) !== false)
			{
				// ... let's try finding the class
				foreach ($searchPaths as $searchPath)
				{
					$path =
						$this->getProjectRoot() . '/' .
						$searchPath . '/' . $this->getLeafname($class) . '.php';
					// If it is found, load it and exit out
					if (file_exists($path))
					{
						require_once $path;
						$loaded = true;
						break 2;
					}
				}
			}
		}

		return $loaded;
	}

	protected function getLeafname($class)
	{
		return substr($class, strrpos($class, '\\') + 1);
	}

	/**
	 * Is this class in our app namespace?
	 * 
	 * @param string $class
	 * @return boolean
	 */
	public function ourNamespace($class)
	{
		return substr($class, 0, strlen(self::PREFIX)) == self::PREFIX;
	}

	protected function getProjectRoot()
	{
		return realpath(__DIR__ . '/..');
	}
}

spl_autoload_register(
	function($class)
	{
		$loader = new \Awooga\Autoloader();
		if ($loader->ourNamespace($class))
		{
			$loader->mainLoader($class) || $loader->testLoader($class);
		}
	}
);