<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for Roles for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevRoleUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		$this->rbac_admin = null;
		$this->rbac_review = null;
		$this->global_roles = null;
		$this->flipped_global_roles = null;
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getRbacAdmin() {
		if ($this->rbac_admin === null) {
			require_once("Services/AccessControl/classes/class.ilRbacAdmin.php");
			$this->rbac_admin = new ilRbacAdmin();
		}
		
		return $this->rbac_admin;
	}
	
	public function getRbacReview() {
		if ($this->rbac_review === null) {
			require_once("Services/AccessControl/classes/class.ilRbacReview.php");
			$this->rbac_review = new ilRbacReview();
		}
		
		return $this->rbac_review;
	}
	
	public function getGlobalRoles() {
		if ($this->global_roles === null) {
			$roles = $this->getRbacReview()->getGlobalRoles();
			
			$res = $this->db->query("SELECT obj_id, title FROM object_data "
								   ." WHERE ".$this->db->in("obj_id", $roles, false, "integer")
								   );
			
			$this->global_roles = array();
			while ($rec = $this->db->fetchAssoc($res)) {
				$this->global_roles[$rec["obj_id"]] = $rec["title"];
			}
		}

		return $this->global_roles;
	}
	
	// all "functional roles" (from spx-concept) that could be assigned to a
	// user in the user administration.
	public function getFunctionalRolesForLocalUserAdministration() {
		return array_filter($this->getGlobalRoles(), function ($title) {
			return in_array($title, gevSettings::$FUNCTIONAL_ROLES);
		});
	}
	
	// all "country roles" (from spx-concept) that could be assigned to a
	// user in the user administration.
	public function getCountryRolesForLocalUserAdministration() {
		return array_filter($this->getGlobalRoles(), function ($title) {
			return in_array($title, gevSettings::$COUNTRY_ROLES);
		});
	}
	
	public function getFlippedGlobalRoles() {
		if ($this->flipped_global_roles === null) {
			$this->flipped_global_roles = array_flip($this->getGlobalRoles());
		}
	
		return $this->flipped_global_roles;
	}
	
	public function assignUserToGlobalRole($a_user_id, $a_role_title) {
		$roles = $this->getFlippedGlobalRoles();
		
		if (!array_key_exists($a_role_title, $roles)) {
			$this->log->write("gevRoleUtils::assignUserToGlobalRole: Could not assign user "
							 .$a_user_id." to unknown role ".$a_role_title);
			return;
		}
		
		$role_id = $roles[$a_role_title];
		gevRoleUtils::getRbacAdmin()->assignUser($role_id, $a_user_id);
		
		global $ilAppEventHandler;
		$ilAppEventHandler->raise('Services/GEV',
			'assignGlobalRole',
			array('user_id' => $a_user_id,
				  'role_id' => $role_id
				  )
			);
	}
	
	public function deassignUserFromGlobalRole($a_user_id, $a_role_title) {
		$roles = $this->getFlippedGlobalRoles();
		
		if (!array_key_exists($a_role_title, $roles)) {
			$this->log->write("gevRoleUtils::assignUserToGlobalRole: Could not assign user "
							 .$a_user_id." to unknown role ".$a_role_title);
			return;
		}
		
		$role_id = $roles[$a_role_title];
		gevRoleUtils::getRbacAdmin()->deassignUser($role_id, $a_user_id);
		
		global $ilAppEventHandler;
		$ilAppEventHandler->raise('Services/GEV',
			'deassignGlobalRole',
			array('user_id' => $a_user_id,
				  'role_id' => $role_id
				  )
			);
	}
	
	public function getGlobalRolesOf($a_user_id) {
		return $this->getRbacReview()->assignedGlobalRoles($a_user_id);
	}
	
	public function getLocalRoleIdsAndTitles($a_obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$rbac_review = $this->getRbacReview();

		$rolf = $rbac_review->getRoleFolderOfObject(gevObjectUtils::getRefId($a_obj_id));

		if (!isset($rolf["ref_id"]) or !$rolf["ref_id"]) {
			throw new Exception("gevRoleUtils::getLocalRoleIdsAndTitles: Could not load role folder.");
		}
		
		$roles = $rbac_review->getRolesOfRoleFolder($rolf["ref_id"], false);
		$res = $this->db->query( "SELECT obj_id, title FROM object_data "
								." WHERE ".$this->db->in("obj_id", $roles, false, "integer"));
		$ret = array();
		while ($rec = $this->db->fetchAssoc($res)) {
			$ret[$rec["obj_id"]] = $rec["title"];
		}
		return $ret;
	}
	
	/* Get the id of a role by name, returns null if no such role exists. */
	public function getRoleIdByName($a_role_name) {
		$res = $this->db->query( "SELECT od.obj_id"
								."  FROM object_data od"
								." WHERE title = ".$this->db->quote($a_role_name, "text")
								."   AND type = 'role'"
								);
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["obj_id"];
		}
		return null;
	}
	
	public function createGlobalRole($a_role_name, $a_role_desc = "") {
		return self::createRoleInFolder(ROLE_FOLDER_ID, $a_role_name, $a_role_desc);
	}
	
	public function createLocalRole($a_ref_id, $a_role_name, $a_role_desc = "") {
		$rolf = $this->getRbacReview()->getRoleFolderOfObject($a_ref_id);
		if (!isset($rolf["ref_id"]) or !$rolf["ref_id"]) {
			throw new Exception("gevRoleUtils::getLocalRoleIdsAndTitles: Could not load role folder.");
		}
		$folder_ref_id = $rolf["ref_id"];
		return self::createRoleInFolder($folder_ref_id, $a_role_name, $a_role_desc);
	}
	
	public function createRoleInFolder($a_folder_ref_id, $a_role_name, $a_role_desc = "") {
		require_once("Services/Object/classes/class.ilObjectFactory.php");
		$a_role_name = trim($a_role_name);
		if (self::roleExistsInFolder($a_folder_ref_id, $a_role_name)) {
			throw new ilException("Role $a_role_name already exists in folder '$a_folder_ref_id'.");
		}
		$rolf = ilObjectFactory::getInstanceByRefId($a_folder_ref_id);
		return $rolf->createRole($a_role_name, $a_role_desc);
	}

	public function roleExistsInFolder($a_folder_ref_id, $a_role_name) {
		$a_role_name = trim($a_role_name);
		$role_ids = $this->getRbacReview()->getRolesOfRoleFolder($a_folder_ref_id);
		foreach ($role_ids as $id) {
			if ($a_role_name == ilObject::_lookupTitle($id))
				return true;
		}
		return false;
	}

	public function grantPermissionsForAllObjectsBelow($a_object_type ,$a_ref_id, $a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

		$this->getRbacReview();
		$this->getRbacAdmin();

		$children = $this->getAllObjectsBelow($a_object_type, array($a_ref_id));
		foreach($children as $child) {
			$this->grantPermissionsFor($child["ref_id"], $a_role_name, $a_permissions);
		}
	}

	public function getAllObjectsBelow($a_object_type, $a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT DISTINCT od.obj_id obj_id, c.child ref_id "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE ".$ilDB->in("p.child", $a_ref_ids, false, "integer")
			."   AND od.type = ".$ilDB->quote($a_object_type,"text")
			);
			
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}

	public function grantPermissionsFor($a_ref_id, $a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");


		$role = $this->getRoleIdByName($a_role_name);
		if (!$role) {
			throw new Exception("gevOrgUnitUtils::grantPermissionFor: unknown role name '".$a_role_name);
		}


		$cur_ops = $this->rbac_review->getRoleOperationsOnObject($role, $a_ref_id);
		$grant_ops = ilRbacReview::_getOperationIdsByName($a_permissions);

		$new_ops = array_unique(array_merge($grant_ops,$cur_ops));
		$this->rbac_admin->revokePermission($a_ref_id, $role);
		$this->rbac_admin->grantPermission($role, $new_ops, $a_ref_id);
	}

}

?>