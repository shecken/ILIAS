<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

class UserOrguFunctionConfigDB {

	protected $db;
	protected $insert_statement;

	public function __construct(\ilDB $db)
	{
		$this->db = $db;
		$this->insert_statement = $this->db->prepareManip(
			'INSERT INTO '
			.self::TABLE
			.'('.self::COLUMN_ROLE.','.self::COLUMN_FUNCTION.')'
			.'VALUES (?,?)',['text','text']);
	}

	const TABLE = 'xuoi_user_orgu_config';

	const COLUMN_FUNCTION = 'function';
	const COLUMN_ROLE = 'role';

	public function load()
	{
		$config = new UserOrguFunctionConfig();
		foreach ($this->loadSuperiorFunctions() as $function) {
			$config->addSuperiorFunction($function);
		}
		foreach ($this->loadEmployeeFunctions() as $function) {
			$config->addEmployeeFunction($function);
		}
		return $config;
	}

	public function save(UserOrguFunctionConfig $config)
	{
		$this->truncateTable();
		$this->saveSuperiorFunctions($config->superiorFunctions());
		$this->saveEmployeeFunctions($config->employeeFunctions());
	}

	protected function loadFunctionsByRole($role)
	{
		assert('is_string($role)');
		$q = 	'SELECT '.self::COLUMN_FUNCTION
				.'	FROM '.self::TABLE
				.'	WHERE '.self::COLUMN_ROLE.' = '.$this->db->quote($role,'text');
		$return = [];
		$res = $this->db->query($q);
		while($rec = $this->db->fetchAssoc($res)) {
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
		$this->saveFunctionsByRole(UserOrguFunctionConfig::SUPERIOR_ROLE,$functions);
	}

	protected function saveEmployeeFunctions(array $functions)
	{
		$this->saveFunctionsByRole(UserOrguFunctionConfig::EMPLOYEE_ROLE,$functions);
	}

	protected function saveFunctionsByRole($role, array $functions)
	{
		assert('is_string($role)');
		$this->db->executeMultiple($this->insert_statement,
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