<?php

namespace CaT\IliasUOITestObjects;

class Rbac
{

	public $storage = [];

	public function assignUser($role_id, $usr_id)
	{
		if (!isset($this->storage[$usr_id])) {
			$this->storage[$usr_id] = [];
		}
		if (!array_search($role_id, $this->storage[$usr_id])) {
			$this->storage[$usr_id][] = $role_id;
		}
	}

	public function deassignUser($role_id, $usr_id)
	{
		if (isset($this->storage[$usr_id])) {
			$val = array_search($role_id, $this->storage[$usr_id]);
			if (false !== $val) {
				unset($this->storage[$usr_id][$val]);
			}
		}
	}

	public function assignedGlobalRoles($usr_id)
	{
		if (isset($this->storage[$usr_id])) {
			return $this->storage[$usr_id];
		}
		return [];
	}
}
