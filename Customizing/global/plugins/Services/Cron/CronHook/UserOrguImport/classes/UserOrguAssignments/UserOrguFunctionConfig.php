<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

class UserOrguFunctionConfig {
	const SUPERIOR_ROLE = 'superior_role';
	const EMPLOYEE_ROLE = 'employee_role';

	protected $superior_functions = [];
	protected $employee_functions = [];

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

	public function roleForFunction($function) {
		assert('is_string($function)');
		if(in_array($function, $this->superior_functions)) {
			return self::SUPERIOR_ROLE;
		}
		if(in_array($function, $this->employee_functions)) {
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
		if(in_array($function,$this->employee_functions)) {
			throw new Exception('function '.$function.' allready set employee');
		}
		$this->superior_functions[] = $function;
	}

	public function addEmployeeFunction($function)
	{
		assert('is_string($function)');
		if(in_array($function,$this->superior_functions)) {
			throw new Exception('function '.$function.' allready set superior');
		}
		$this->employee_functions[] = $function;
	}
}