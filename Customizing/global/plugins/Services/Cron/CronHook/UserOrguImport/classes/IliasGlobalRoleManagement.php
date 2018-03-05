<?php

namespace CaT\IliasUserOrguImport;

/**
 * Manipulate ilias-side role assignments. Wrapper around rbac.
 */
class IliasGlobalRoleManagement
{

	protected $rbac_admin;
	protected $rbac_review;

	public function __construct(\ilRbacAdmin $rbac_admin, \ilRbacReview $rbac_review)
	{
		$this->rbac_admin = $rbac_admin;
		$this->rbac_review = $rbac_review;
	}

	/**
	 * Assign a role to user.
	 *
	 * @param	int	$role_id
	 * @param	User\IliasUser	$usr
	 */
	public function assignToUser($role_id, User\IliasUser $usr)
	{
		assert('is_int($role_id)');
		$this->rbac_admin->assignUser($role_id, $usr->iliasId());
	}

	/**
	 * Diassign a role to user.
	 *
	 * @param	int	$role_id
	 * @param	User\IliasUser	$usr
	 */
	public function deassignFromUser($role_id, User\IliasUser $usr)
	{
		assert('is_int($role_id)');
		$this->rbac_admin->deassignUser($role_id, $usr->iliasId());
	}


	/**
	 * Assign a role to user.
	 *
	 * @param	int	$role_id
	 * @param	User\IliasUser	$usr
	 */
	public function assignByUsrId($role_id, $usr_id)
	{
		assert('is_int($role_id)');
		assert('is_int($usr_id)');
		$this->rbac_admin->assignUser($role_id, $usr_id);
	}

	/**
	 * Diassign a role to user.
	 *
	 * @param	int	$role_id
	 * @param	User\IliasUser	$usr
	 */
	public function deassignByUsrId($role_id, $usr_id)
	{
		assert('is_int($role_id)');
		assert('is_int($usr_id)');
		$this->rbac_admin->deassignUser($role_id, $usr_id);
	}

	/**
	 * Get a list of all global roles assigned to user.
	 *
	 * @param	User\IliasUser	$usr
	 */
	public function assignedRoles(User\IliasUser $usr)
	{
		return $this->rbac_review->assignedGlobalRoles($usr->iliasId());
	}

	/**
	 * Get all existing global roles.
	 *
	 * @return	int[]
	 */
	public function getGlobalRoles()
	{
		return $this->rbac_review->getGlobalRoles();
	}

	/**
	 * Set operations at some ref for a role.
	 *
	 * @param	int	$role_id
	 * @param	int	$ref_id
	 * @param	sting[]	$operations
	 */
	public function setNewOperationsForRoleIdAtRefId($role_id, $ref_id, array $operations)
	{
		assert('is_int($role_id)');
		assert('is_int($ref_id)');
		$op_ids = \ilRbacReview::_getOperationIdsByName($operations);
		$this->rbac_admin->revokePermission($ref_id, $role_id);
		$this->rbac_admin->grantPermission($role_id, $op_ids, $ref_id);
	}

	/**
	 * Get the number of users assigned to any ilias role.
	 *
	 * @param	int	$role_id
	 * @return	int
	 */
	public function numberOfAssignedUsers(array $role_ids)
	{
		return (int)$this->rbac_review->getNumberOfAssignedUsers($role_ids);
	}
}
