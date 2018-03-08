<?php

use CaT\UserOrguImport\User\User as U;

class UserTest extends PHPUnit_Framework_TestCase
{

	protected static $properties = ['p1' => 'b'];
	protected static $properties_other = ['p1' => 'b','foo' => 'bar'];

	public function test_init()
	{
		require_once dirname(__FILE__).'/Fixture/UserTestIdentifier.php';
		return new U(self::$properties, new UserTestIdentifier());
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
	public function test_properties($u)
	{
		$this->assertEquals($u->properties(), self::$properties);
		$u = $u->withProperties(self::$properties_other);
		$this->assertEquals($u->properties(), self::$properties_other);
	}
}
