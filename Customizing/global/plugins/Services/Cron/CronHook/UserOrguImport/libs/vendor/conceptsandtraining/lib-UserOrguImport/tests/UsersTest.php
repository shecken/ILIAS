<?php

use CaT\UserOrguImport\User\User as U;
use CaT\UserOrguImport\User\Users as US;

class UsersTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		require_once dirname(__FILE__).'/Fixture/UserTestIdentifier.php';
		$this->ident = new UserTestIdentifier();
	}


	public function test_init()
	{
		return new US($this->ident);
	}

	/**
	 * @depends test_init
	 */
	public function test_add_user($us)
	{

		$ua = new U(['p1' => 'va1'], $this->ident);
		$ub = new U(['p1' => 'vb1'], $this->ident);
		$us->add($ua);
		$us->add($ub);

		$ua_2 = new U(['p1' => 'va1','p2' => 'va2'], $this->ident);
		$ub_2 = new U(['p1' => 'vb1','p2' => 'vb2'], $this->ident);
		$this->assertTrue($us->contains($ua_2));
		$this->assertTrue($us->contains($ub_2));

		$this->assertFalse($us->contains(new U(['p1' => 'vc1'], $this->ident)));
		return $us;
	}

	/**
	 * @depends test_add_user
	 */
	public function test_iterate($us)
	{
		$visited = [];
		$check = ['va1_' => ['p1' => 'va1'], 'vb1_' => ['p1' => 'vb1']];
		foreach ($us as $key => $value) {
			$visited[] = $key;

			$this->assertEquals($value->properties(), $check[$key]);
		}
		$this->assertEquals($visited, ['va1_','vb1_']);
	}
}
