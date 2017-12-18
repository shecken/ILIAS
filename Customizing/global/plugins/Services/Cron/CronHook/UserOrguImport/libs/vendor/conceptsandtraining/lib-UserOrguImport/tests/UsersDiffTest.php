<?php

use CaT\UserOrguImport\User\User as U;
use CaT\UserOrguImport\User\Users as US;
use CaT\UserOrguImport\User\UsersDifference as UD;

class UsersDiffTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/UserTestIdentifier.php';
		$this->ident = new UserTestIdentifier();
	}


	public function test_init()
	{
		return new UD($this->usersLeft(), $this->usersRight());
	}

	/**
	 * @depends test_init
	 */
	public function test_diff($ud)
	{
		$this->checkAdd($ud->toCreate(), ['vg1_vg2','vh1_'], $this->usersRight());
		$this->checkRemove($ud->toDelete(), ['va1_va2','_vb2']);
		$this->checkChange($ud->toChange(), ['vd1_vd2' => ['p1' => 'vd1','p2' => 'vd2','p3' => 'vd3','foo' => 'bar'],'ve1_ve2A' => ['p1' => 've1','p2' => 've2A','p3' => 've3']]);
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

	protected function checkChange($us, $check)
	{
		$id = $this->ident;
		$check_visited = [];
		foreach ($us as $u) {
			$check_visited[] = $id->digestId($u);
			$this->assertEquals($u->properties(), $check[$id->digestId($u)]);
		}
		$this->assertEquals($check_visited, array_keys($check));
	}

	protected function usersLeft()
	{
		$us = new US($this->ident);
		$us->add(new U(['p1' => 'va1','p2' => 'va2','p3' => 'va3'], $this->ident));
		$us->add(new U([			 'p2' => 'vb2','p3' => 'vb3'], $this->ident));
		$us->add(new U(['p1' => 'vc1','p2' => 'vc2','p3' => 'vc3'], $this->ident));
		$us->add(new U(['p1' => 'vd1','p2' => 'vd2','p3' => 'vd3'], $this->ident));
		$us->add(new U(['p1' => 've1','p2' => 've2','p3' => 've3'], $this->ident));
		$us->add(new U(['p1' => 'vf1','p2' => 'vf2','p3' => 'vf3'], $this->ident));
		//$us->add(new U('g',['p1' => 'vg1','p2' => 'vg2','p3' => 'vg3']));
		//$us->add(new U('h',['p1' => 'vh1','p2' => 'vh2','p3' => 'vh3']));
		return $us;
	}
	protected function usersRight()
	{
		$us = new US($this->ident);
		//$us->add(new U('a',['p1' => 'va1','p2' => 'va2','p3' => 'va3']));
		//$us->add(new U('b',['p1' => 'vb1','p2' => 'vb2','p3' => 'vb3']));
		$us->add(new U(['p1' => 'vc1','p2' => 'vc2','p3' => 'vc3'], $this->ident));
		$us->add(new U(['p1' => 'vd1','p2' => 'vd2','p3' => 'vd3','foo' => 'bar'], $this->ident));
		$us->add(new U(['p1' => 've1','p2' => 've2A','p3' => 've3'], $this->ident));
		$us->add(new U(['p1' => 'vf1',				'p3' => 'vf3'], $this->ident));
		$us->add(new U(['p1' => 'vg1','p2' => 'vg2','p3' => 'vg3'], $this->ident));
		$us->add(new U(['p1' => 'vh1',				'p3' => 'vh3'], $this->ident));
		return $us;
	}
}
