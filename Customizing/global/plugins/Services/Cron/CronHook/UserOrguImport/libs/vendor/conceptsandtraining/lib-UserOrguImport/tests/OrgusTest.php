<?php

use CaT\UserOrguImport\Orgu\AdjacentOrgUnit as AOU;
use CaT\UserOrguImport\Orgu\AdjacentOrgUnits as OUS;
use CaT\UserOrguImport\Items\Item as Item;

class OrgusTest extends PHPUnit_Framework_TestCase
{
	const PARENT_ID = 'pid';

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/OrguTestIdentifier.php';
		$this->ident = new OrguTestIdentifier();
	}

	public function test_init()
	{

		return new OUS($this->ident);
	}

	/**
	 * @depends test_init
	 */
	public function test_add_orgu($ous)
	{
		$oa = new AOU(['p1' => 'va1','p2' => 'va2'], $this->ident, ['p1' => self::PARENT_ID]);
		$ob = new AOU(['p1' => 'vb1','p2' => 'vb2'], $this->ident, ['p1' => self::PARENT_ID]);
		$ous->add($oa);
		$ous->add($ob);

		$oa_2 = new AOU(['p1' => 'va1'], $this->ident, ['p1' => self::PARENT_ID]);
		$ob_2 = new AOU(['p1' => 'vb1'], $this->ident, ['p1' => self::PARENT_ID]);

		$this->assertTrue($ous->contains($oa_2));
		$this->assertTrue($ous->contains($ob_2));

		$this->assertFalse($ous->contains(new AOU(['p1' => 'vc1'], $this->ident, ['p1' => self::PARENT_ID])));
		return $ous;
	}

	/**
	 * @depends test_add_orgu
	 */
	public function test_iterate($ous)
	{
		$visited = [];
		$check = ['va1' => ['p1' => 'va1','p2' => 'va2'], 'vb1' => ['p1' => 'vb1','p2' => 'vb2']];
		foreach ($ous as $key => $value) {
			$visited[] = $key;
			$this->assertEquals($value->properties(), $check[$key]);
			$this->assertEquals($value->parentOrguIdProperties(), ['p1' => self::PARENT_ID]);
		}
		$this->assertEquals($visited, ['va1','vb1']);
	}
}
