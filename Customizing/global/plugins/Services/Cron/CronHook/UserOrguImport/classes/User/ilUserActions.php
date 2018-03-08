<?php

namespace CaT\IliasUserOrguImport\User;

class ilUserActions
{

	protected $f;

	public function __construct(UserFactory $f)
	{
		$this->f = $f;
	}

	public function addExternRole($title, $desc, array $roles)
	{
		assert('is_string($title)');
		assert('is_string($desc)');
		$roles_filtered = [];
		$rc_previous = $this->roleConfigurator();
		$rc_new = $this->roleConfigurator();
		$global_roles = $rc_previous->globalRoleIds();

		foreach ($roles as $value) {
			if (in_array($value, $global_roles)) {
				$roles_filtered[] = (int)$value;
			}
		}
		$rc_new->add($title, (string)$desc, $roles_filtered);

		if ($this->f->UdfWrapper()->fieldId(UdfWrapper::PROP_FLAG_KU) !== null) {
			$u_locator = $this->f->UserLocator();
			$user_ids = $u_locator->relevantUserIdsWithExternRole($title);
			$users = $u_locator->usersByUserIds($user_ids);
			$updater = $this->f->UserRoleUpdater();
			$curr_date = date('Y-m-d');
			foreach ($users as $user) {
				$exit_date = $user->properties()[UdfWrapper::PROP_EXIT_DATE];
				if ($exit_date === '' || $curr_date < $exit_date) {
					$updater->updateRolesOfUserChangedConfig($user, $rc_previous, $rc_new);
				}
			}
		}

		return $rc_new;
	}

	public function updateExternRole($ext_role_id, $title, $desc, array $roles)
	{
		assert('is_int($ext_role_id)');
		assert('is_string($title)');
		assert('is_string($desc)');
		$roles_filtered = [];
		$rc_previous = $this->roleConfigurator();
		$rc_new = $this->roleConfigurator();
		$global_roles = $rc_previous->globalRoleIds();

		foreach ($roles as $value) {
			if (in_array($value, $global_roles)) {
				$roles_filtered[] = (int)$value;
			}
		}
		$rc_new->update($ext_role_id, (string)$title, (string)$desc, $roles_filtered);
		if ($this->f->UdfWrapper()->fieldId(UdfWrapper::PROP_FLAG_KU) !== null) {
			$u_locator = $this->f->UserLocator();
			$previous_ext_role = $rc_previous->externRoleForExternRoleId($ext_role_id);
			$new_ext_role = $rc_new->externRoleForExternRoleId($ext_role_id);
			$user_ids = array_unique(array_merge($u_locator->relevantUserIdsWithExternRole($previous_ext_role), $u_locator->relevantUserIdsWithExternRole($new_ext_role)));
			$users = $u_locator->usersByUserIds($user_ids);
			$updater = $this->f->UserRoleUpdater();
			$curr_date = date('Y-m-d');
			foreach ($users as $user) {
				$exit_date = $user->properties()[UdfWrapper::PROP_EXIT_DATE];
				if ($exit_date === '' || $curr_date < $exit_date) {
					$updater->updateRolesOfUserChangedConfig($user, $rc_previous, $rc_new);
				}
			}
		}
		return $rc_new;
	}

	public function deleteExternRole($ext_role_id)
	{
		assert('is_int($ext_role_id)');
		$roles_filtered = [];
		$rc_previous = $this->roleConfigurator();
		$rc_new = $this->roleConfigurator();
		$global_roles = $rc_previous->globalRoleIds();
		$rc_new->delete($ext_role_id, (string)$title, (string)$desc, $roles_filtered);
		if ($this->f->UdfWrapper()->fieldId(UdfWrapper::PROP_FLAG_KU) !== null) {
			$u_locator = $this->f->UserLocator();
			$user_ids = $u_locator->relevantUserIdsWithExternRole($rc_previous->externRoleForExternRoleId($ext_role_id));
			$users = $u_locator->usersByUserIds($user_ids);
			$updater = $this->f->UserRoleUpdater();
			$curr_date = date('Y-m-d');
			foreach ($users as $user) {
				$exit_date = $user->properties()[UdfWrapper::PROP_EXIT_DATE];
				if ($exit_date === '' || $curr_date < $exit_date) {
					$updater->updateRolesOfUserChangedConfig($user, $rc_previous, $rc_new);
				}
			}
		}
		return $rc_new;
	}

	protected function roleConfigurator()
	{
		return $this->f->RoleConfiguration();
	}
}
