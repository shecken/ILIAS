<?php

use CaT\UserOrguImport\UserOrguAssignment\Assignment as A;

class AssignmentTest extends PHPUnit_Framework_TestCase
{

	protected static $properties = ['user' => 'u', 'orgu' => 'o', 'role' => 'rol','foo' => 'bar'];

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/AssignmentTestIdentifier.php';
		$this->ident = new AssignmentTestIdentifier();
	}

	public function test_init()
	{

		return new A(self::$properties, $this->ident);
	}

	/**
	 * @depends test_init
	 */
	public function test_id($u)
	{
		$this->assertEquals($u->properties(), self::$properties);
		return $u;
	}



	/**
	 * @depends test_id
	 */
	public function test_insuff_id($u)
	{
		$properties = self::$properties;
		unset($properties['role']);
		try {
			new A(self::$properties, $this->ident);
			$this->assertFalse('has not thrown');
		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}
}
