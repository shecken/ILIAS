<?php

use CaT\IliasUserOrguImport\UserOrguAssignments as UserOrgu;

class UserOrguConfigTest extends PHPUnit_Framework_TestCase
{
	public function test_init()
	{
		$cfg =  new UserOrgu\UserOrguFunctionConfig();
		$this->assertEquals([], $cfg->superiorFunctions());
		$this->assertEquals([], $cfg->employeeFunctions());
		return $cfg;
	}

	/**
	 * @depends test_init
	 */
	public function test_add_superior()
	{
		$cfg = $this->test_init();
		$cfg->addSuperiorFunction('fun1');
		$cfg->addSuperiorFunction('fun2');
		$this->assertEquals(['fun1','fun2'], $cfg->superiorFunctions());
		$this->assertEquals(UserOrgu\UserOrguFunctionConfig::SUPERIOR_ROLE, $cfg->roleForFunction('fun2'));
		$this->assertNull($cfg->roleForFunction('foo'));
	}

	/**
	 * @depends test_init
	 */
	public function test_add_employee()
	{
		$cfg = $this->test_init();
		$cfg->addEmployeeFunction('fun1');
		$cfg->addEmployeeFunction('fun2');
		$this->assertEquals(['fun1','fun2'], $cfg->employeeFunctions());
		$this->assertEquals(UserOrgu\UserOrguFunctionConfig::EMPLOYEE_ROLE, $cfg->roleForFunction('fun1'));
		$this->assertNull($cfg->roleForFunction('foo'));
	}

	/**
	 * @depends test_add_superior
	 * @depends test_add_employee
	 */
	public function test_re_add_employee_to_superior()
	{
		$cfg = $this->test_init();
		$cfg->addEmployeeFunction('fun1');
		$cfg->addEmployeeFunction('fun2');
		$cfg->addSuperiorFunction('sfun1');
		try {
			$cfg->addSuperiorFunction('fun2');
			$this->assertFalse('has not thrown');
		} catch (UserOrgu\Exception $e) {
		}
	}

	/**
	 * @depends test_add_superior
	 * @depends test_add_employee
	 */
	public function test_re_add_superior_to_employee()
	{
		$cfg = $this->test_init();
		$cfg->addSuperiorFunction('fun1');
		$cfg->addSuperiorFunction('fun2');
		$cfg->addEmployeeFunction('sfun1');
		try {
			$cfg->addEmployeeFunction('fun2');
			$this->assertFalse('has not thrown');
		} catch (UserOrgu\Exception $e) {
		}
	}

	/**
	 * @depends test_init
	 */
	public function test_by_arrays()
	{
		$superiors = ['sa','sb','sc'];
		$employees = ['ea','eb','ec'];
		$cfg = UserOrgu\UserOrguFunctionConfig::getInstanceByArrays($superiors, $employees);
		$this->assertEquals($superiors, $cfg->superiorFunctions());
		$this->assertEquals($employees, $cfg->employeeFunctions());
		foreach ($superiors as $value) {
			$this->assertEquals(UserOrgu\UserOrguFunctionConfig::SUPERIOR_ROLE, $cfg->roleForFunction($value));
		}
		foreach ($employees as $value) {
			$this->assertEquals(UserOrgu\UserOrguFunctionConfig::EMPLOYEE_ROLE, $cfg->roleForFunction($value));
		}
	}
}
