<?php
require_once 'Modules/ManualAssessment/interfaces/AccessControl/interface.ManualAssessmentAccessHandler.php';
require_once 'Services/AccessControl/classes/class.ilObjRole.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
/**
 * @inheritdoc
 * Deal with ilias rbac-system
 */
class ilManualAssessmentAccessHandler implements ManualAssessmentAccessHandler
{

	protected $handler;
	protected $admin;
	protected $review;

	protected $mass_settings_cache = array();
	protected $mass_global_permissions_cache = array();
	protected $mass_user_permissions_cache = array();
	protected $employee_cache;

	const DEFAULT_ROLE = 'il_mass_member';

	public function __construct(ilAccessHandler $handler, ilRbacAdmin $admin, ilRbacReview $review, ilObjUser $usr, ilDB $db, ilRbacAdmin $rbacadmin)
	{
		$this->handler = $handler;
		$this->admin = $admin;
		$this->review = $review;
		$this->usr = $usr;
		$this->usr_utils = gevUserUtils::getInstanceByObj($this->usr);
		$this->db = $db;
		$this->rbacadmin = $rbacadmin;
	}

	/**
	 * Can the current ilias user perform an operation on some manual assessment?
	 *
	 * @param	ilObjManualAssessment	$mass
	 * @param	string	$operation
	 * @return bool
	 */
	public function checkAccessToObj(ilObjManualAssessment $mass, $operation, $use_cache = false)
	{
		if ($use_cache) {
			return $this->fromCacheCheckAccessToObj($mass, $operation);
		}
		return $this->checkAccessOfUserToObj($this->usr, $mass, $operation);
	}

	protected function fromCacheCheckAccessToObj(ilObjManualAssessment $mass, $operation)
	{
		$mid = $mass->getId();
		if (!isset($this->mass_global_permissions_cache[$mid][$operation])) {
			$this->mass_global_permissions_cache[$mid][$operation]
		 		= $this->checkAccessToObj($mass, $operation);
		}
		return $this->mass_global_permissions_cache[$mid][$operation];
	}

	/**
	 * @inheritdoc
	 */
	public function checkAccessOfUserToObj(ilObjUser $usr, ilObjManualAssessment $mass, $operation)
	{
		return $this->handler->checkAccessOfUser($usr->getId(), $operation, '', $mass->getRefId(), 'mass');
	}

	/**
	 * @inheritdoc
	 */
	public function initDefaultRolesForObject(ilObjManualAssessment $mass)
	{
		$rolf_obj = $mass->createRoleFolder();

		// CREATE ADMIN ROLE
		$role_obj = $rolf_obj->createRole($this->getRoleTitleByObj($mass), "");
		$admin_id = $role_obj->getId();

		$rolt_obj_id = $this->getRoltId();
		$this->rbacadmin->copyRoleTemplatePermissions($rolt_obj_id, ROLE_FOLDER_ID, $rolf_obj->getRefId(), $role_obj->getId());
	}

	/**
	 * @inheritdoc
	 */
	public function assignUserToMemberRole(ilObjUser $usr, ilObjManualAssessment $mass)
	{
		return $this->admin->assignUser($this->getMemberRoleIdForObj($mass), $usr->getId());
	}

	/**
	 * @inheritdoc
	 */
	public function deassignUserFromMemberRole(ilObjUser $usr, ilObjManualAssessment $mass)
	{
		return $this->admin->deassignUser($this->getMemberRoleIdForObj($mass), $usr->getId());
	}

	protected function getRoleTitleByObj(ilObjManualAssessment $mass)
	{
		return self::DEFAULT_ROLE.'_'.$mass->getRefId();
	}

	protected function getMemberRoleIdForObj(ilObjManualAssessment $mass)
	{
		return current($this->review->getLocalRoles($mass->getRefId()));
	}

	protected function getRoltId()
	{
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type = 'rolt' AND title = ".$this->db->quote(self::DEFAULT_ROLE, "text");

		$res = $this->db->getRow($query, DB_FETCHMODE_ASSOC);

		return $res["obj_id"];
	}

	public function mayViewUserIn($usr_id, ilObjManualAssessment $mass, $use_cache = false)
	{
		if ($use_cache) {
			return $this->fromCacheMayViewUserIn($usr_id, $mass);
		}
		if ($this->checkAccessToObj($mass, 'read_learning_progress')) {
			return true;
		}
		if ($mass->getSettings()->superiorView()) {
			return in_array($usr_id, $this->usr_utils->getEmployees());
		}
		return false;
	}

	protected function fromCacheMayViewUserIn($usr_id, ilObjManualAssessment $mass)
	{
		global $ilLog;
		if ($this->fromCacheCheckAccessToObj($mass, 'read_learning_progress')) {
			return true;
		}
		$this->cacheSettingsAndEmployees($mass, $this->usr_utils);
		if ($this->mass_settings_cache[$mass->getId()]->superiorView()) {
			return in_array($usr_id, $this->employee_cache);
		}
		return false;
	}

	public function mayGradeUserIn($usr_id, ilObjManualAssessment $mass, $use_cache = false)
	{
		if ($use_cache) {
			return $this->fromCacheMayGradeUserIn($usr_id, $mass);
		}
		if ($this->checkAccessToObj($mass, 'edit_learning_progress')) {
			return true;
		}
		return $this->calcMayGradeUserIn(
			$mass->getSettings()->superiorExaminate(),
			in_array($usr_id, $this->usr_utils->getEmployees()),
			$mass->getSettings()->gradeSelf(),
			$usr_id
		);
	}

	protected function fromCacheMayGradeUserIn($usr_id, ilObjManualAssessment $mass)
	{
		if ($this->fromCacheCheckAccessToObj($mass, 'edit_learning_progress')) {
			return true;
		}
		$this->cacheSettingsAndEmployees($mass, $this->usr_utils);
		return $this->calcMayGradeUserIn(
			$this->mass_settings_cache[$mass->getId()]->superiorExaminate(),
			in_array($usr_id, $this->employee_cache),
			$this->mass_settings_cache[$mass->getId()]->gradeSelf(),
			$usr_id == $this->usr->getId()
		);
	}

	protected function calcMayGradeUserIn($superior_examinate, $is_employee, $grade_self, $usr_id)
	{
		assert('is_bool($superior_examinate)');
		assert('is_bool($is_employee)');
		assert('is_bool($grade_self)');
		assert('is_numeric($usr_id)');
		$aux = false;
		if ($superior_examinate) {
			$aux = $aux || $is_employee;
		}
		if ($grade_self) {
			$aux = $aux || $usr_id == $this->usr->getId();
		}
		return $aux;
	}

	protected function cacheSettingsAndEmployees($mass, $usr_utils)
	{

		$mass_id = $mass->getId();
		if (!isset($this->mass_settings_cache[$mass_id])) {
			$this->mass_settings_cache[$mass_id] = $mass->getSettings();
		}
		if (!is_array($this->employee_cache)) {
			$this->employee_cache = $usr_utils->getDirectEmployees();
		}
	}
}
