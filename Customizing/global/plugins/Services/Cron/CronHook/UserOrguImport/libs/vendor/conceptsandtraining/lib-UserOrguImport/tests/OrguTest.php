<?php

use CaT\UserOrguImport\Orgu\AdjacentOrgUnit as AOU;

class OrguTest extends PHPUnit_Framework_TestCase
{

	protected static $parent_id_properties = ['p1' => 'pb'];
	protected static $properties = ['p1' => 'b'];
	protected static $properties_other = ['p1' => 'b','foo' => 'bar'];

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/OrguTestIdentifier.php';
		$this->ident = new OrguTestIdentifier();
	}

	public function test_init()
	{
		return new AOU(self::$properties, $this->ident, self::$parent_id_properties);
	}

	/**
	 * @depends test_init
	 */
	public function test_id($ou)
	{
		$this->assertEquals($ou->parentOrguIdProperties(), self::$parent_id_properties);
		$this->assertEquals($ou->properties(), self::$properties);
		return $ou;
	}

	/**
	 * @depends test_id
	 */
	public function test_metadata($ou)
	{
		$this->assertEquals($ou->properties(), self::$properties);
		$ou = $ou->withProperties(self::$properties_other);
		$this->assertEquals($ou->properties(), self::$properties_other);
		$this->assertEquals($ou->parentOrguIdProperties(), self::$parent_id_properties);
	}
}
