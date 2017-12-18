<?php

namespace CaT\IliasUserOrguImport\User;

use CaT\IliasUserOrguImport as Base;
/**
 * Set the Configuration of extern roles (Sammelrolle)
 * Add, remove, modify ilias-role-assignments of a given extern role.
 */
class RoleConfiguration
{

	const EXT_ROLE = 'ext_role';
	const DESC = 'description';

	protected $db;
	protected $global_roles;

	protected $assignments;
	protected $extern_roles;
	protected $extern_roles_ids;

	public function __construct(\ilDB $db, Base\IliasGlobalRoleManagement $grm)
	{
		$this->db = $db;
		$this->global_roles = $grm->getGlobalRoles();
		$this->load();
	}

	/**
	 * Get the ids of all availiable global roles.
	 *
	 * @return	int[]
	 */
	public function globalRoleIds()
	{
		return $this->global_roles;
	}

	/**
	 * Create a new extern role
	 *
	 * @param	string	$ext_role
	 * @param	string	$desc
	 * @param	int[]	$role_ids
	 */
	public function add($ext_role, $desc, array $role_ids)
	{
		assert('is_string($ext_role)');
		if (in_array('#'.$ext_role, array_keys($this->extern_roles_ids))) {
			throw new \InvalidArgumentException($ext_role.' allready exists');
		}
		if (!$this->globalRolesExist($role_ids)) {
			throw new \InvalidArgumentException('trying to add nonexisting global roles to ext role '.$ext_role_id);
		}
		$id = $this->createExternRole($ext_role, $desc);
		$this->insertRolesFor($id, $role_ids);
		$this->load();
	}

	protected function createExternRole($ext_role, $desc)
	{
		assert('is_string($ext_role)');
		assert('is_string($desc)');
		$id = (int)$this->db->nextId('xuoi_ext_roles');
		$this->db->manipulate('INSERT INTO xuoi_ext_roles (id,ext_role,description)'
							.'	VALUES ('.$this->db->quote($id, 'integer').','.$this->db->quote($ext_role, 'text').','.$this->db->quote($desc, 'text').')');
		return $id;
	}


	/**
	 * Delete a extern role.
	 *
	 * @param	int	$ext_role_id
	 */
	public function delete($ext_role_id)
	{
		assert('is_int($ext_role_id)');
		$this->db->manipulate('DELETE FROM xuoi_ext_roles WHERE id = '.$this->db->quote($ext_role_id, 'integer'));
		$this->deleteRolesFor($ext_role_id);
		$this->load();
	}

	/**
	 * Update a extern role
	 *
	 * @param	string	$ext_role
	 * @param	string	$desc
	 * @param	int[]	$role_ids
	 */
	public function update($ext_role_id, $ext_role, $description, array $role_ids)
	{
		assert('is_int($ext_role_id)');
		assert('is_string($ext_role)');
		if (isset($this->extern_roles_ids['#'.$ext_role]) && $this->extern_roles_ids['#'.$ext_role] !== $ext_role_id) {
			throw new \InvalidArgumentException($ext_role.' allready exists');
		}
		if (!$this->globalRolesExist($role_ids)) {
			throw new \InvalidArgumentException('trying to add nonexisting global roles to ext role '.$ext_role_id);
		}
		$ext_role_prev = $this->externRoleForExternRoleId($ext_role_id);
		$this->updateProperties($ext_role_id, $ext_role, $description);
		$this->deleteRolesFor($ext_role_id);
		$this->insertRolesFor($ext_role_id, $role_ids);
		$this->load();
	}

	protected function globalRolesExist(array $global_roles)
	{
		return count(array_intersect($global_roles, $this->globalRoleIds())) === count($global_roles);
	}

	protected function updateProperties($ext_role_id, $ext_role, $description)
	{
		$query = 'UPDATE xuoi_ext_roles'
				.'	SET description = '.$this->db->quote($description, 'text')
				.'		,ext_role = '.$this->db->quote($ext_role, 'text')
				.'	WHERE id = '.$this->db->quote($ext_role_id, 'integer');
		$this->db->manipulate($query);
	}

	protected function insertRolesFor($ext_role_id, array $role_ids)
	{
		assert('is_int($ext_role_id)');
		if (count($role_ids) > 0) {
			$role_ids = array_unique($role_ids);
			$global_roles = $this->global_roles;
			$role_ids = array_filter($role_ids, function ($role_id) use ($global_roles) {
				return in_array($role_id, $global_roles);
			});
			$data = [];
			foreach ($role_ids as $role_id) {
				$data[] = [$ext_role_id, $role_id];
			}
			$statement = $this->db->prepareManip("INSERT INTO xuoi_ext_role_config (ext_role_id, role_id) VALUES (?,?)", array("integer", "integer"));
			$this->db->executeMultiple($statement, $data);
		}
	}

	protected function deleteRolesFor($extern_role_id)
	{
		$this->db->manipulate('DELETE FROM xuoi_ext_role_config WHERE ext_role_id = '.$this->db->quote($extern_role_id, 'integer'));
	}

	/**
	 * Get all stored extern role titles
	 *
	 * @return	string[]
	 */
	public function externRoles()
	{
		return array_map(function ($arg) {
				return substr($arg,1);
			},array_keys($this->extern_roles_ids));
	}

	/**
	 * Does an extern role with the corresponding title exist?
	 *
	 * @return	bool
	 */
	public function externRoleExists($ext_role)
	{
		assert('is_string($ext_role)');
		return isset($this->extern_roles_ids['#'.$ext_role]);
	}

	/**
	 * Get all stored extern role ids
	 *
	 * @return	int[]
	 */
	public function externRoleIds()
	{
		return array_values($this->extern_roles_ids);
	}

	/**
	 * Get an extern role id for a title
	 *
	 * @param 	string	$ext_role
	 * @return	int|null
	 */
	public function externRoleIdForExternRole($ext_role)
	{
		assert('is_string($ext_role)');
		return $this->extern_roles_ids['#'.$ext_role];
	}

	/**
	 * Get an extern role title for a given id
	 *
	 * @param 	int	$ext_role_id
	 * @return	string|null
	 */
	public function externRoleForExternRoleId($ext_role_id)
	{
		assert('is_int($ext_role_id)');
		return $this->extern_roles[$ext_role_id][self::EXT_ROLE];
	}

	/**
	 * Get an extern role description for a given id
	 *
	 * @param 	int	$ext_role_id
	 * @return	string|null
	 */
	public function externRoleDescription($ext_role)
	{
		assert('is_string($ext_role)');
		$ext_role_id = $this->externRoleIdForExternRole($ext_role);
		if (!isset($this->extern_roles[$ext_role_id])) {
			throw new \InvalidArgumentException($ext_role.' does not exist');
		}
		return $this->extern_roles[$ext_role_id][self::DESC];
	}

	/**
	 * Get the role ids associated with an extern role title
	 *
	 * @param 	string	$ext_role
	 * @return	int[]
	 */
	public function roleIdsFor($ext_role)
	{
		assert('is_string($ext_role)');
		$ext_role_id = $this->externRoleIdForExternRole($ext_role);
		if ($ext_role_id === null) {
			return [];
		}
		return $this->roleIdsForExternRoleId($ext_role_id);
	}

	/**
	 * Get the role ids associated with an extern role id
	 *
	 * @param 	int	$ext_role_id
	 * @return	int[]
	 */
	public function roleIdsForExternRoleId($ext_role_id)
	{
		$ass = $this->assignments[$ext_role_id];
		if (is_array($ass)) {
			return $ass;
		}
		return [];
	}

	protected function load()
	{
		$this->assignments = [];
		$query = 'SELECT ext_role_id,role_id'
				.'	FROM xuoi_ext_role_config';
		$res = $this->db->query($query);
		while ($rec = $this->db->fetchAssoc($res)) {
			if (!isset($this->assignments[$rec['ext_role']])) {
				$this->assignments[$rec['ext_role']] = [];
			}
			if (in_array($rec['role_id'], $this->global_roles)) {
				$this->assignments[(int)$rec['ext_role_id']][] = (int)$rec['role_id'];
			}
		}
		$this->extern_roles = [];
		$this->extern_roles_ids = [];
		$query = 'SELECT id,ext_role, description'
				.'	FROM xuoi_ext_roles';
		$res = $this->db->query($query);
		while ($rec = $this->db->fetchAssoc($res)) {
			$this->extern_roles[(int)$rec['id']] = [self::DESC => $rec['description'], self::EXT_ROLE => $rec['ext_role']];
			$this->extern_roles_ids['#'.$rec['ext_role']] = (int)$rec['id'];
		}
	}
}
