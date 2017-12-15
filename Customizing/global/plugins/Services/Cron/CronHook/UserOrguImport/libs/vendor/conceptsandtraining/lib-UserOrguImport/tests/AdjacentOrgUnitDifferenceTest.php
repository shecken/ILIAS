<?php

use CaT\UserOrguImport\Orgu\AdjacentOrgUnit as O;
use CaT\UserOrguImport\Orgu\AdjacentOrgUnits as OS;
use CaT\UserOrguImport\Orgu\AdjacentOrgUnitsDifference as OD;

class AdjacentOrgUnitDifferenceTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/OrguTestIdentifier.php';
		$this->ident = new OrguTestIdentifier();
	}

	public function test_init()
	{
		return new OD($this->orgusLeft(), $this->orgusRight(), $this->ident);
	}

	/**
	 * @depends test_init
	 */
	public function test_diff($ud)
	{
		$this->checkAdd($ud->toCreate(), ['vg1','vh1'], $this->orgusRight());
		$this->checkRemove($ud->toDelete(), ['va1','vb1']);
		$this->checkChange($ud->toChange(), ['vd1' => ['p1' => 'vd1','p2' => 'vd2','p3' => 'vd3','foo' => 'bar'],'ve1' => ['p1' => 've1','p2' => 've2A','p3' => 've3']], ['vd1' => ['p1' => 'o4a'], 've1' => ['p1' => 'o5']]);
	}

	protected function checkAdd($us, array $check, $ref)
	{
		$id = $this->ident;
		$check_visited = [];
		foreach ($us as $u) {
			$check_visited[] = $id->digestId($u);
			$this->assertEquals($u->properties(), $ref->itemByIdDigest($id->digestId($u))->properties());
		}

		$this->assertEquals($check_visited, $check);
	}

	protected function checkRemove($us, array $check)
	{
		$id = $this->ident;
		$check_visited = [];
		foreach ($us as $u) {
			$check_visited[] = $id->digestId($u);
		}
		$this->assertEquals($check_visited, $check);
	}

	protected function checkChange($us, $check, $check_parents)
	{
		$id = $this->ident;
		$check_visited = [];
		foreach ($us as $u) {
			$check_visited[] = $id->digestId($u);
			$this->assertEquals($u->properties(), $check[$id->digestId($u)]);
			$this->assertEquals($u->parentOrguIdProperties(), $check_parents[$id->digestId($u)]);
		}
		$this->assertEquals($check_visited, array_keys($check));
	}

	protected function orgusLeft()
	{
		$us = new OS($this->ident);
		$us->add(new O(['p1' => 'va1','p2' => 'va2','p3' => 'va3'], $this->ident, ['p1' => 'o1']));
		$us->add(new O(['p1' => 'vb1', 'p2' => 'vb2','p3' => 'vb3'], $this->ident, ['p1' => 'o2']));
		$us->add(new O(['p1' => 'vc1','p2' => 'vc2','p3' => 'vc3'], $this->ident, ['p1' => 'o3']));
		$us->add(new O(['p1' => 'vd1','p2' => 'vd2','p3' => 'vd3'], $this->ident, ['p1' => 'o4']));
		$us->add(new O(['p1' => 've1','p2' => 've2','p3' => 've3'], $this->ident, ['p1' => 'o5']));
		$us->add(new O(['p1' => 'vf1','p2' => 'vf2','p3' => 'vf3'], $this->ident, ['p1' => 'o6']));
		//$us->add(new U('g',['p1' => 'vg1','p2' => 'vg2','p3' => 'vg3']));
		//$us->add(new U('h',['p1' => 'vh1','p2' => 'vh2','p3' => 'vh3']));
		return $us;
	}
	protected function orgusRight()
	{
		$us = new OS($this->ident);
		//$us->add(new U('a',['p1' => 'va1','p2' => 'va2','p3' => 'va3']));
		//$us->add(new U('b',['p1' => 'vb1','p2' => 'vb2','p3' => 'vb3']));
		$us->add(new O(['p1' => 'vc1','p2' => 'vc2','p3' => 'vc3'], $this->ident, ['p1' => 'o3']));
		$us->add(new O(['p1' => 'vd1','p2' => 'vd2','p3' => 'vd3','foo' => 'bar'], $this->ident, ['p1' => 'o4a']));
		$us->add(new O(['p1' => 've1','p2' => 've2A','p3' => 've3'], $this->ident, ['p1' => 'o5']));
		$us->add(new O(['p1' => 'vf1',				'p3' => 'vf3'], $this->ident, ['p1' => 'o6']));
		$us->add(new O(['p1' => 'vg1','p2' => 'vg2','p3' => 'vg3'], $this->ident, ['p1' => 'o7']));
		$us->add(new O(['p1' => 'vh1',				'p3' => 'vh3'], $this->ident, ['p1' => 'o8']));
		return $us;
	}
}
