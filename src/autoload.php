<?php

// Autoload for app-level classes, traits and interfaces

spl_autoload_register(
	function($class)
	{
		$root = __DIR__;

		$prefix = 'Awooga';
		if (substr($class, 0, strlen($prefix)) == $prefix)
		{
			$slashes = str_replace('\\', '/', $class);
			$path = $root . '/' . str_replace($prefix . '/', '', $slashes) . '.php';
			require_once $path;
		}
	}
);