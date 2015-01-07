<?php

namespace Awooga\Traits;

use Awooga\Exceptions\TrivialException;

trait Validation
{
	/**
	 * Checks to see if the parameter is a string, raises a TrivialException if not
	 * 
	 * @param mixed $string
	 */
	protected function isString($string)
	{
		if (!is_string($string))
		{
			throw new TrivialException("This field is expected to be a string");			
		}
	}

	/**
	 * Checks to see if the parameter is an array, raises a TrivialException if not
	 * 
	 * @param mixed $array
	 * @throws TrivialException
	 */
	protected function isArray($array)
	{
		if (!is_array($array))
		{
			throw new TrivialException("This field is expected to be an array");			
		}		
	}

	/**
	 * Checks to see if a required field is entered, raises a TrivialException if not
	 * 
	 * @param mixed $data
	 * @param string $name
	 * @throws TrivialException
	 */
	protected function isRequired($data, $name)
	{
		if (!$data)
		{
			throw new TrivialException("The '{$name}' field is required");
		}		
	}

	protected function validateLength($data, $name, $length)
	{
		if (strlen($data) > $length)
		{
			throw new TrivialException("The '{$name}' field cannot be longer than {$length} characters");			
		}
	}	
}