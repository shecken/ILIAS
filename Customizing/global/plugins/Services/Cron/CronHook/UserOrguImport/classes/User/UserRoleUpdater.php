<?php

#DONE

namespace CaT\IliasUserOrguImport\User;

use CaT\IliasUserOrguImport as Base;

/**
 * Handles user updates through external roles.
 */
class UserRoleUpdater
{

	protected $ra;

	public function __construct(Base\IliasGlobalRoleManagement $ra, Base\Log\Log $log)
	{
		$this->ra = $ra;
		$this->log = $log;
	}

	/**
	 * A user has changed. Update his roles according to a given role-configuration.
	 *
	 * @param	IliasUser	$previous
	 * @param	IliasUser	$desired
	 * @param	RoleConfiguration	$rc
	 * @return	void
	 */
	public function updateRolesOfChangedUser(IliasUser $previous, IliasUser $desired, RoleConfiguration $rc)
	{
		if ($previous->iliasId() !== $desired->iliasId()) {
			throw new \LogicExcepition('ilias-ids do not aggree');
		}
		$previous_extern = $this->externKuFlagOf($previous);
		$desired_extern = $this->externKuFlagOf($desired);
		if($previous_extern !== $desired_extern) {
			foreach ($rc->roleIdsFor($previous_extern) as $role_id) {
				$this->ra->deassignFromUser($role_id, $previous);
			}
			foreach ($rc->roleIdsFor($desired_extern) as $role_id) {
				$this->ra->assignToUser($role_id, $previous);
			}
		}
	}

	/**
	 * A role configuration has changed. Update the roles of a given user according to the change.
	 *
	 * @param	IliasUser	$previous
	 * @param	RoleConfiguration	$role_config_previous
	 * @param	RoleConfiguration	$role_config_new
	 * @return	void
	 */
	public function updateRolesOfUserChangedConfig(IliasUser $user, RoleConfiguration $role_config_previous, RoleConfiguration $role_config_new)
	{
		$diassign = [];
		$assign = [];

		$extern_role = $this->externKuFlagOf($user);
		$diassign = array_merge($diassign, $role_config_previous->roleIdsFor($extern_role));
		$assign = array_merge($assign, $role_config_new->roleIdsFor($extern_role));

		foreach (array_diff($diassign, $assign) as $role_id) {
			$this->ra->deassignFromUser($role_id, $user);
		}
		$assigned = $this->ra->assignedRoles($user);
		foreach (array_diff($assign, $assigned) as $role_id) {
			$this->ra->assignToUser($role_id, $user);
		}
	}

	protected function externKuFlagOf(IliasUser $user)
	{
		return $user->properties()[UdfWrapper::PROP_FLAG_KU];
	}
}
