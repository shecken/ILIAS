<?php

namespace CaT\Plugins\TalentAssessment\Observator;

class ilDB implements DB {
	public function __construct($db) {
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createLocalRoleTemplate($tpl_title, $tpl_description) {
		include_once("./Services/AccessControl/classes/class.ilObjRoleTemplate.php");
		$roltObj = new \ilObjRoleTemplate();
		$roltObj->setTitle($tpl_title);
		$roltObj->setDescription($tpl_description);
		$roltObj->create();
	}

	/**
	 * @inheritdoc
	 */
	public function getRoltId($tpl_title) {
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type = 'rolt' AND title = ".$this->db->quote($tpl_title, "text");

		$res = $this->db->getRow($query, DB_FETCHMODE_ASSOC);

		return $res["obj_id"];
	}

	public function setRoleFolder($tpl_title, $rolf_ref_id) {
		global $rbacadmin;

		$tpl_object = $this->getRoleTemplateObject($tpl_title);
		$rbacadmin->assignRoleToFolder($tpl_object->getId(), $rolf_ref_id, 'n');
		$rbacadmin->setProtected($rolf_ref_id, $tpl_object->getId(), 'y');
	}

	public function setDefaultPermissions($tpl_title, $rolf_ref_id, array $permissions) {
		global $rbacadmin;

		$tpl_object = $this->getRoleTemplateObject($tpl_title);
		$rbacadmin->setRolePermission($tpl_object->getId(), "xtas", \ilRbacReview::_getOperationIdsByName($permissions), $rolf_ref_id);
	}

	protected function getRoleTemplateObject($tpl_title) {
		$obj_id = \ilObject::_getIdsForTitle($tpl_title, "rolt");
		$tpl_object = \ ilObjectFactory::getInstanceByObjId((int)$obj_id[0]);

		return $tpl_object;
	}
}