<?php

use CaT\UserOrguImport\UserOrguAssignment\AssignmentsDifference as AD;
use CaT\UserOrguImport\UserOrguAssignment\Assignments as ASS;
use CaT\UserOrguImport\UserOrguAssignment\Assignment as A;

class AssignmentsDiffTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/AssignmentTestIdentifier.php';
		$this->ident = new AssignmentTestIdentifier();
	}

	public function test_init()
	{
		return new AD($this->assLeft(), $this->assRight());
	}

	/**
	 * @depends test_init
	 */
	public function test_diff($ud)
	{
		$this->checkAdd($ud->toCreate(), ['ud_ra_od','uc_rh_oa'], $this->assRight());
		$this->checkRemove($ud->toDelete(), ['ua_ra_oa','ua_rb_ob']);
		$this->checkChange($ud->toChange(), [
			'ua_re_od' => ['user' => 'ua','role' => 're','orgu' =>'od', 'p' => 'a1' ]
			,'ua_rf_od' => ['user' => 'ua','role' => 'rf','orgu' =>'od', 'p' => 'a1' ]
			]);
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


	protected function assLeft()
	{
		$us = new ASS($this->ident);
		$us->add(new A(['user' => 'ua','role' => 'ra','orgu' =>'oa', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ua','role' => 'rb','orgu' =>'ob', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ua','role' => 'rb','orgu' =>'oc', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ud','role' => 'rb','orgu' =>'od', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ua','role' => 're','orgu' =>'od', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ua','role' => 'rf','orgu' =>'od', 'p' => 'a' ], $this->ident));

		return $us;
	}
	protected function assRight()
	{
		$us = new ASS($this->ident);

		$us->add(new A(['user' => 'ua','role' => 'rb','orgu' =>'oc', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ud','role' => 'rb','orgu' =>'od', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'ua','role' => 're','orgu' =>'od', 'p' => 'a1' ], $this->ident));
		$us->add(new A(['user' => 'ua','role' => 'rf','orgu' =>'od', 'p' => 'a1' ], $this->ident));
		$us->add(new A(['user' => 'ud','role' => 'ra','orgu' =>'od', 'p' => 'a' ], $this->ident));
		$us->add(new A(['user' => 'uc','role' => 'rh','orgu' =>'oa', 'p' => 'a' ], $this->ident));
		return $us;
	}
}
