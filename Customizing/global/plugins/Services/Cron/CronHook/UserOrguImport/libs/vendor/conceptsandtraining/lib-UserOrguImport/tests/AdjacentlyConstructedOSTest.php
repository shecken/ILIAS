<?php

use CaT\UserOrguImport\Orgu\AdjacentlyConstructedOS as OS;
use CaT\UserOrguImport\Orgu\OrgUnit as OU;
use CaT\UserOrguImport\Orgu\AdjacentOrgUnit as AOU;

class AdjacentlyConstructedOSTest extends PHPUnit_Framework_TestCase
{
	const ROOT_ID_1 = 'root_id_1';
	const ROOT_ID_2 = 'root_id_2';

	protected $ident;

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/OrguTestIdentifier.php';
		$this->ident = new OrguTestIdentifier();
	}

	public function test_init()
	{
		return new OS($this->ident);
	}

	/**
	 * @depends test_init
	 */
	public function test_add_root($os)
	{
		$this->assertEquals($os->rootOrgus(), []);
		$os->addRootOrgu(new OU(['p1' => self::ROOT_ID_1], $this->ident));
		$this->assertEquals($this->mapToId($os->rootOrgus()), [self::ROOT_ID_1]);
		$os->addRootOrgu(new OU(['p1' => self::ROOT_ID_2], $this->ident));
		$this->assertEquals($this->mapToId($os->rootOrgus()), [self::ROOT_ID_1,self::ROOT_ID_2]);

		return $os;
	}

	/**
	 * @depends test_add_root
	 */
	public function test_add_sub_orgu($os)
	{
		// we may insert suborgus to nonexistent parents
		$os->addOrgu(new AOU(['p1' => 'sub_sub_root_1_1'], $this->ident, ['p1' => 'sub_root_1']));
		$os->addOrgu(new AOU(['p1' => 'sub_root_1'], $this->ident, ['p1' => self::ROOT_ID_1]));
		$os->addOrgu(new AOU(['p1' => 'sub_root_2'], $this->ident, ['p1' =>  self::ROOT_ID_1]));

		// two orgus with same id may not be insertet into the tree in any combination
		try {
			$os->addOrgu(new AOU(['p1' => 'sub_root_1'], $this->ident, ['p1' => self::ROOT_ID_1]));
			$this->assertFalse('has not thrown');
		} catch (LogicException $e) {
			$this->assertTrue(true);
		}

		try {
			$os->addOrgu(new AOU(['p1' => 'sub_sub_root_1_1'], $this->ident, ['p1' => 'sub_root_2']));
			$this->assertFalse('has not thrown');
		} catch (LogicException $e) {
			$this->assertTrue(true);
		}

		$this->assertEquals($this->ident->digestId($os->orgu(['p1' => 'sub_sub_root_1_1'])), 'sub_sub_root_1_1');
		$this->assertEquals($this->ident->digestId($os->orgu(['p1' => 'sub_root_1'])), 'sub_root_1');
		$this->assertEquals($this->ident->digestId($os->orgu(['p1' => 'sub_root_2'])), 'sub_root_2');
		return $os;
	}

	/**
	 * @depends test_add_sub_orgu
	 */
	public function test_fin($os)
	{
		$this->assertEquals($this->ident->digestId($os->orgu(['p1' => 'sub_sub_root_1_1'])), 'sub_sub_root_1_1');
		$this->assertEquals($this->ident->digestId($os->orgu(['p1' => 'sub_root_1'])), 'sub_root_1');
		$this->assertEquals($this->ident->digestId($os->orgu(['p1' => 'sub_root_2'])), 'sub_root_2');

		$this->assertEquals($this->mapToId($os->subOrgus($os->orgu(['p1' => self::ROOT_ID_1]))), ['sub_root_1','sub_root_2']);

		$this->assertEquals($this->mapToId($os->subOrgus($os->orgu(['p1' =>'sub_root_1']))), ['sub_sub_root_1_1']);

		$this->assertEquals($this->mapToId($os->subOrgus($os->orgu(['p1' => 'sub_root_2']))), []);
		$this->assertEquals($this->mapToId($os->subOrgus($os->orgu(['p1' => 'sub_sub_root_1_1']))), []);

		$this->assertNull($os->orgu(['p1' =>'not there']));
	}

	/**
	 * @depends test_init
	 */
	public function test_faulty_tree($os)
	{

		$this->assertTrue($os->treeConsistent());

		try {
			// disconnected
			$os->addOrgu(new AOU(['p1' => 'sub_sub_root_1_1'], $this->ident, ['p1' => 'sub_root_1']));
			$os->addOrgu(new AOU(['p1' => 'sub_root_2'], $this->ident, ['p1' => self::ROOT_ID_1]));

			$this->assertFalse($os->treeConsistent());

			$os->subOrgus($os->orgu(['p1' => 'sub_root_2']));
			$this->assertFalse('has not thrown');
		} catch (LogicException $e) {
			$this->assertTrue(true);
		}

		try {
			// cyclic
			$os->addOrgu(new AOU(['p1' => 'orgu_1'], $this->ident, ['p1' => 'orgu_2']));
			$os->addOrgu(new AOU(['p1' => 'orgu_2'], $this->ident, ['p1' => 'orgu_3']));
			$os->addOrgu(new AOU(['p1' => 'orgu_3'], $this->ident, ['p1' => 'orgu_1']));

			$this->assertFalse($os->treeConsistent());

			$os->subOrgus($os->orgu(['p1' => 'orgu_3']));
			$this->assertFalse('has not thrown');
		} catch (LogicException $e) {
			$this->assertTrue(true);
		}
	}

	protected function mapToId(array $orgus)
	{
		$return = [];
		foreach ($orgus as $orgu) {
			$return[] = $this->ident->digestId($orgu);
		}
		return $return;
	}
}
