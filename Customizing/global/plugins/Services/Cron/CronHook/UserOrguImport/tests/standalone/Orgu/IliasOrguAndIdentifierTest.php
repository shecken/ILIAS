<?php

use CaT\IliasUserOrguImport\Orgu as Orgu;

class IliasOrguAndIdentifierTest extends PHPUnit_Framework_TestCase
{
	const ID_1 = 123;

	protected static $properties_1 = [Orgu\OrguAMDWrapper::PROP_ID => '01'];
	protected static $properties_2 = [Orgu\OrguAMDWrapper::PROP_ID => '02'];
	protected static $properties_1_a = [Orgu\OrguAMDWrapper::PROP_ID => '01', 'foo' => 'bar'];
	protected static $parent_properties =  [Orgu\OrguAMDWrapper::PROP_ID => 'p01'];
	protected static $parent_properties_a =  [Orgu\OrguAMDWrapper::PROP_ID => 'p01a'];

	public function test_init()
	{
		return new Orgu\IliasOrgu(self::$properties_1, $this->identifier(), self::$parent_properties, self::ID_1);
	}

	/**
	 * @depends test_init
	 */
	public function test_properties($orgu)
	{
		$this->assertEquals($orgu->refId(), self::ID_1);
		$this->assertEquals($orgu->properties(), self::$properties_1);
		$this->assertEquals($orgu->withProperties(self::$properties_1_a)->properties(), self::$properties_1_a);
		$this->assertEquals($orgu->parentOrguIdProperties(), self::$parent_properties);
		$this->assertEquals($orgu
				->withParentOrguIdProperties(self::$parent_properties_a)
				->parentOrguIdProperties(), self::$parent_properties_a);
		return $orgu;
	}

	/**
	 * @depends test_properties
	 */
	public function test_same()
	{
		$orgu_1 = $this->test_init();
		$orgu_2 = new Orgu\IliasOrgu(self::$properties_2, $this->identifier(), self::$parent_properties, self::ID_1);
		$this->assertFalse($this->identifier()->same($orgu_1, $orgu_2));
		$this->assertFalse($this->identifier()->same($orgu_2, $orgu_1));
		$orgu_2 = new Orgu\IliasOrgu(self::$properties_1_a, $this->identifier(), self::$parent_properties, self::ID_1+1);
		$this->assertTrue($this->identifier()->same($orgu_1, $orgu_2));
		$this->assertTrue($this->identifier()->same($orgu_2, $orgu_1));
	}

	public function test_faulty_init()
	{
		try {
			new Orgu\IliasOrgu(['nonsense' => 'stuff'], $this->identifier(), self::$parent_properties, self::ID_1);
			$this->assertFalse('has not thrown');
		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

	protected function identifier()
	{
		return new Orgu\OrguIdentifier();
	}
}
