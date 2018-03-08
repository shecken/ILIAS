<?php

namespace CaT\IliasUserOrguImport;

class ExitConfig
{

	protected $exit_role;

	const EXIT_ROLE_ID = 'xuoi_exit_role_id';

	public function __construct(\ilSetting $set)
	{
		$this->set = $set;
	}

	public function setExitRoleId($role_id)
	{
		assert('is_int($role_id)');
		$this->set->set(self::EXIT_ROLE_ID, $role_id);
	}

	public function exitRoleId()
	{
		return (int)$this->set->get(self::EXIT_ROLE_ID);
	}
}
