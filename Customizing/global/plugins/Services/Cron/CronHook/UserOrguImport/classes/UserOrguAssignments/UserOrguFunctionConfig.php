<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

class UserOrguFunctionConfig
{
	const SUPERIOR_ROLE = 'superior_role';
	const EMPLOYEE_ROLE = 'employee_role';

	protected $superior_functions = [];
	protected $employee_functions = [];

	protected $superior_global;
	protected $employee_global;

	public static function getInstanceByArrays(array $superior_functions, array $employee_functions)
	{
		$instance = new self();
		foreach ($superior_functions as $function) {
			$instance->addSuperiorFunction($function);
		}
		foreach ($employee_functions as $function) {
			$instance->addEmployeeFunction($function);
		}
		return $instance;
	}

	public function roleForFunction($function)
	{
		assert('is_string($function)');
		if (in_array($function, $this->superior_functions)) {
			return self::SUPERIOR_ROLE;
		}
		if (in_array($function, $this->employee_functions)) {
			return self::EMPLOYEE_ROLE;
		}
		return null;
	}

	public function superiorFunctions()
	{
		return array_unique($this->superior_functions);
	}

	public function employeeFunctions()
	{
		return array_unique($this->employee_functions);
	}

	public function addSuperiorFunction($function)
	{
		assert('is_string($function)');
		if (in_array($function, $this->employee_functions)) {
			throw new Exception('function '.$function.' allready set employee');
		}
		$this->superior_functions[] = $function;
	}

	public function addEmployeeFunction($function)
	{
		assert('is_string($function)');
		if (in_array($function, $this->superior_functions)) {
			throw new Exception('function '.$function.' allready set superior');
		}
		$this->employee_functions[] = $function;
	}

	public function withSuperiorGlobalRoleId($role_id)
	{
		assert('is_int($role_id)');
		$other = clone $this;
		$other->superior_global = $role_id;
		return $other;
	}

	public function withEmployeeGlobalRoleId($role_id)
	{
		assert('is_int($role_id)');
		$other = clone $this;
		$other->employee_global = $role_id;
		return $other;
	}

	public function superiorGlobalRoleId()
	{
		return $this->superior_global;
	}

	public function employeeGlobalRoleId()
	{
		return $this->employee_global;
	}
}
