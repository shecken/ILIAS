<?php
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */


require_once 'Services/Object/classes/class.ilObject.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettingsStorageDB.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
require_once 'Modules/ManualAssessment/classes/AccessControl/class.ilManualAssessmentAccessHandler.php';
require_once 'Modules/ManualAssessment/classes/FileStorage/class.ilManualAssessmentFileStorage.php';
class ilObjManualAssessment extends ilObject
{

	protected $lp_active = null;
	protected $file_storage;
	protected $settings_storage;
	protected $members_storage;
	protected $access_handler;

	protected $settings;
	protected $info_settings;

	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		global $ilAccess, $ilDB, $rbacadmin, $rbacreview, $ilUser;
		$this->type = 'mass';
		parent::__construct($a_id, $a_call_by_reference);
		$this->settings_storage = new ilManualAssessmentSettingsStorageDB($ilDB);
		$this->members_storage =  new ilManualAssessmentMembersStorageDB($ilDB);
		$this->access_handler = new ilManualAssessmentAccessHandler(
			$ilAccess,
			$rbacadmin,
			$rbacreview,
			$ilUser,
			$ilDB,
			$rbacadmin
		);
	}

	/**
	 * @inheritdoc
	 */
	public function create()
	{
		parent::create();
		$this->settings = new ilManualAssessmentSettings($this);
		$this->settings_storage->createSettings($this->settings);
	}

	/**
	 * @inheritdoc
	 */
	public function read()
	{
		parent::read();
		global $ilDB;
		$settings_storage = new ilManualAssessmentSettingsStorageDB($ilDB);
		$this->settings = $settings_storage->loadSettings($this);
		$this->info_settings = $settings_storage->loadInfoSettings($this);
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		if (!$this->settings) {
			$this->settings = $this->settings_storage->loadSettings($this);
		}
		return $this->settings;
	}

	public function getInfoSettings()
	{
		if (!$this->info_settings) {
			$this->info_settings = $this->settings_storage->loadInfoSettings($this);
		}
		return $this->info_settings;
	}

	/**
	 * Get the members object associated with this.
	 *
	 * @return	ilManualAssessmentMembers
	 */
	public function loadMembers()
	{
		return $this->members_storage->loadMembers($this);
	}

	/**
	 * Update the members object associated with this.
	 *
	 * @param	ilManualAssessmentMembers	$members
	 */
	public function updateMembers(ilManualAssessmentMembers $members)
	{
		$members->updateStorageAndRBAC($this->members_storage, $this->access_handler);
	}

	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		$this->settings_storage->deleteSettings($this);
		$this->members_storage->deleteMembers($this);
		$f_storage = $this->getFileStorage();
		$f_storage->delete($f_storage->getAbsolutePath());
		parent::delete();
	}

	/**
	 * @inheritdoc
	 */
	public function update()
	{
		parent::update();
		$this->settings_storage->updateSettings($this->settings);
	}

	public function updateInfo()
	{
		$this->settings_storage->updateInfoSettings($this->info_settings);
	}

	/**
	 * Get the member storage object used by this.
	 *
	 * @return ilManualAssessmentMembersStorage
	 */
	public function membersStorage()
	{
		return $this->members_storage;
	}

	/**
	 * @inheritdoc
	 */
	public function initDefaultRoles()
	{
		$this->access_handler->initDefaultRolesForObject($this);
	}

	/**
	 * Get the access handler of this.
	 *
	 * @return	ManualAssessmentAccessHandler
	 */
	public function accessHandler()
	{
		return $this->access_handler;
	}

	/**
	 * @inheritdoc
	 */
	public function cloneObject($a_target_id, $a_copy_id = 0)
	{
		$new_obj = parent::cloneObject($a_target_id, $a_copy_id);
		$settings = $this->getSettings();
		$info_settings = $this->getInfoSettings();
		$new_settings = new ilManualAssessmentSettings(
			$new_obj,
			$settings->content(),
			$settings->recordTemplate(),
			$settings->fileRequired(),
			$settings->eventTimePlaceRequired(),
			$settings->superiorExaminate(),
			$settings->superiorView(),
			$settings->gradeSelf(),
			$settings->viewSelf(),
			$settings->workInstruction()
		);
		$new_obj->settings = $new_settings;
		$new_info_settings = new ilManualAssessmentInfoSettings(
			$new_obj,
			$info_settings->contact(),
			$info_settings->responsibility(),
			$info_settings->phone(),
			$info_settings->mails(),
			$info_settings->consultationHours()
		);
		$new_obj->settings = $new_settings;
		$new_obj->info_settings = $new_info_settings;
		$new_obj->settings_storage->updateSettings($new_settings);
		$new_obj->settings_storage->updateInfoSettings($new_info_settings);

		$fstorage = $this->getFileStorage();
		if(count($fstorage->readDir()) > 0) {
			$n_fstorage = $new_obj->getFileStorage();
			$n_fstorage->create();
			$fstorage->_copyDirectory($fstorage->getAbsolutePath(), $n_fstorage->getAbsolutePath());
		}
		return $new_obj;
	}

	/**
	 * Check wether the LP is activated for current object.
	 *
	 * @return bool
	 */
	public function isActiveLP()
	{
		if ($this->lp_active === null) {
			require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
			$this->lp_active = ilManualAssessmentLPInterface::isActiveLP($this->getId());
		}
		return $this->lp_active;
	}

	/**
	 * Bubbles up the tree.
	 * Starts from object with id $id.
	 * Ends at root or when a given $type of object is found.
	 *
	 * @param int $id start at this id
	 * @param string[] $types search for these strings
	 *
	 * @return int the obj_id or 0 if root is reached
	 */
	public function getParentContainerIdByType($id, array $types)
	{
		global $tree;
		$node = $tree->getParentNodeData($id);

		while ($node['type'] !== "root") {
			if (in_array($node['type'], $types)) {
				return $node['ref_id'];
			}
			$node = $tree->getParentNodeData($node['ref_id']);
		}
		return 0;
	}

	/**
	 * Get the file storage system
	 *
	 * @return ilManualAssessmentFileStorage
	 */
	public function getFileStorage()
	{
		if ($this->file_storage === null) {
			$this->file_storage = ilManualAssessmentFileStorage::getInstance($this->getId());
		}
		return $this->file_storage;
	}

	/**
	 * Get filestorage for work intruction files
	 *
	 * @return ilManualAssessmentFileStorage
	 */
	public function getWorkIntructionFileStorage()
	{
		if ($this->intruction_file_storage === null) {
			$this->intruction_file_storage = ilManualAssessmentFileStorage::getInstance($this->getId());
		}

		return $this->intruction_file_storage;
	}
}
