<?php

use CaT\IliasUserOrguImport\User as User;

class IliasUserAndIdentifierTest extends PHPUnit_Framework_TestCase
{

	protected static $properties_1 = [User\UdfWrapper::PROP_PNR => 'a'];
	protected static $properties_2 = [User\UdfWrapper::PROP_PNR => 'b'];
	protected static $properties_3 = [User\UdfWrapper::PROP_PNR => 'a'];
	protected static $properties_4 = [User\UdfWrapper::PROP_PNR => 'a'];
	protected static $properties_5 = [User\UdfWrapper::PROP_PNR => 'b'];

	const ID_1 = 123;

	public function test_init()
	{
		return new User\IliasUser(self::$properties_1, $this->identifier(), self::ID_1);
	}

	/**
	 * @depends test_init
	 */
	public function test_properties($usr)
	{
		$this->assertEquals($usr->iliasId(), 123);
		$this->assertEquals($usr->properties(), self::$properties_1);
		return $usr;
	}

	/**
	 * @depends test_properties
	 */
	public function test_same()
	{
		$usr_1 = new User\IliasUser(self::$properties_1, $this->identifier(), self::ID_1);
		$usr_2 = new User\IliasUser(self::$properties_2, $this->identifier(), self::ID_1);
		$usr_3 = new User\IliasUser(self::$properties_3, $this->identifier(), self::ID_1);

		$this->assertTrue($this->identifier()->same($usr_1, $usr_1));
		$this->assertFalse($this->identifier()->same($usr_1, $usr_2));
		$this->assertFalse($this->identifier()->same($usr_2, $usr_1));
		$this->assertTrue($this->identifier()->same($usr_1, $usr_3));
		$this->assertFalse($this->identifier()->same($usr_3, $usr_2));
	}

	protected function identifier()
	{
		return new  User\UserIdentifier();
	}
}
