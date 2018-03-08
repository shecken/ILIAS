<?php

use CaT\IliasUserOrguImport\ErrorReporting\ErrorCollection as ErrorCollection;

class ErrorCollectionTest extends PHPUnit_Framework_TestCase
{
	public function test_init()
	{
		return new ErrorCollection();
	}

	/**
	 * @depends test_init
	 */
	public function test_add_error($e_c)
	{
		$this->assertFalse($e_c->containsErrors());
		$e_c->addError('error 1');
		$this->assertTrue($e_c->containsErrors());
		$e_c->addError('error 2');
		$this->assertTrue($e_c->containsErrors());
		return $e_c;
	}

	/**
	 * @depends test_add_error
	 */
	public function test_iterate($e_c)
	{
		$errors = [];
		foreach ($e_c as $value) {
			$errors[] = $value;
		}
		$this->assertContains('error 1', $errors);
		$this->assertContains('error 2', $errors);
		$this->assertCount(2, $errors);
	}
}
