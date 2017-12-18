<?php

namespace CaT\IliasUserOrguImport\ErrorReporting;

class ErrorCollection implements \Iterator
{

	protected $errors = [];

	public function addError($error)
	{
		assert('is_string($error)');
		$this->errors[] = $error;
	}

	public function containsErrors()
	{
		return count($this->errors) > 0;
	}


	public function numberOfErrors()
	{
		return count($this->errors);
	}

	public function rewind()
	{
		reset($this->errors);
	}

	public function current()
	{
		return current($this->errors);
	}

	public function key()
	{
		return key($this->errors);
	}

	public function next()
	{
		next($this->errors);
	}

	public function valid()
	{
		return key($this->errors) !== null;
	}
}
