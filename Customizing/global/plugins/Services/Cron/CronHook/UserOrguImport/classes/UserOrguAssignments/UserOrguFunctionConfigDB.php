<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

class UserOrguFunctionConfigDB
{

	protected $db;
	protected $insert_statement;
	protected $set;

	public function __construct(\ilDB $db, \ilSetting $set)
	{
		$this->db = $db;
		$this->set = $set;
		$this->insert_statement = $this->db->prepareManip(
			'INSERT INTO '
			.self::TABLE
			.'('.self::COLUMN_ROLE.','.self::COLUMN_FUNCTION.')'
			.'VALUES (?,?)',
			['text','text']
		);
	}

	const TABLE = 'xuoi_user_orgu_config';

	const COLUMN_FUNCTION = 'function';
	const COLUMN_ROLE = 'role';

	const SUPERIOR_GLOBAL_ROLE_ID_SETTING = 'xuoi_superior_global_role_id';
	const EMPLOYEE_GLOBAL_ROLE_ID_SETTING = 'xuoi_employee_global_role_id';

	public function load()
	{
		$config = new UserOrguFunctionConfig();
		foreach ($this->loadSuperiorFunctions() as $function) {
			$config->addSuperiorFunction($function);
		}
		foreach ($this->loadEmployeeFunctions() as $function) {
			$config->addEmployeeFunction($function);
		}

		$superior_global = $this->set->get(self::SUPERIOR_GLOBAL_ROLE_ID_SETTING);
		$employee_global = $this->set->get(self::EMPLOYEE_GLOBAL_ROLE_ID_SETTING);
		if ($superior_global) {
			$config = $config->withSuperiorGlobalRoleId((int)$superior_global);
		}
		if ($employee_global) {
			$config = $config->withEmployeeGlobalRoleId((int)$employee_global);
		}
		return $config;
	}

	public function save(UserOrguFunctionConfig $config)
	{
		$this->truncateTable();
		$this->saveSuperiorFunctions($config->superiorFunctions());
		$this->saveEmployeeFunctions($config->employeeFunctions());

		$this->set->set(self::SUPERIOR_GLOBAL_ROLE_ID_SETTING, $config->superiorGlobalRoleId());
		$this->set->set(self::EMPLOYEE_GLOBAL_ROLE_ID_SETTING, $config->employeeGlobalRoleId());
	}

	protected function loadFunctionsByRole($role)
	{
		assert('is_string($role)');
		$q = 	'SELECT '.self::COLUMN_FUNCTION
				.'	FROM '.self::TABLE
				.'	WHERE '.self::COLUMN_ROLE.' = '.$this->db->quote($role, 'text');
		$return = [];
		$res = $this->db->query($q);
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[] = $rec[self::COLUMN_FUNCTION];
		}
		return $return;
	}

	protected function loadSuperiorFunctions()
	{
		return $this->loadFunctionsByRole(UserOrguFunctionConfig::SUPERIOR_ROLE);
	}

	protected function loadEmployeeFunctions()
	{
		return $this->loadFunctionsByRole(UserOrguFunctionConfig::EMPLOYEE_ROLE);
	}

	protected function saveSuperiorFunctions(array $functions)
	{
		$this->saveFunctionsByRole(UserOrguFunctionConfig::SUPERIOR_ROLE, $functions);
	}

	protected function saveEmployeeFunctions(array $functions)
	{
		$this->saveFunctionsByRole(UserOrguFunctionConfig::EMPLOYEE_ROLE, $functions);
	}

	protected function saveFunctionsByRole($role, array $functions)
	{
		assert('is_string($role)');
		$this->db->executeMultiple(
			$this->insert_statement,
			array_map(function ($function) use ($role) {
				return [$role,$function];
			}, $functions)
		);
	}

	protected function truncateTable()
	{
		$this->db->manipulate('DELETE FROM '.self::TABLE.' WHERE TRUE');
	}
}
