<?php
/**
 * A settings storage handler to write mass settings to db.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
require_once 'Modules/ManualAssessment/interfaces/Settings/interface.ilManualAssessmentSettingsStorage.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentInfoSettings.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
class ilManualAssessmentSettingsStorageDB implements ilManualAssessmentSettingsStorage
{
	const MASS_SETTINGS_TABLE = "mass_settings";
	const MASS_SETTINGS_INFO_TABLE = "mass_info_settings";

	/**
	 * @var ilDB
	 */
	protected $db;
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createSettings(ilManualAssessmentSettings $settings)
	{
		$values = array
				( "obj_id" => array("integer", $settings->getId())
				, "content" => array("text", $settings->content())
				, "record_template" => array("text", $settings->recordTemplate())
				, "file_required" => array("integer", $settings->fileRequired())
				, "event_time_place_required" => array("integer", $settings->eventTimePlaceRequired())
				, "superior_examinate" => array("integer", $settings->superiorExaminate())
				, "superior_view" => array("integer", $settings->superiorView())
				, "grade_self" => array("integer", $settings->gradeSelf())
				, 'view_self' => array("integer", $settings->viewSelf())
				);
		$this->db->insert(self::MASS_SETTINGS_TABLE, $values);

		$values = array("obj_id" => array("integer", $settings->getId()));
		$this->db->insert(self::MASS_SETTINGS_INFO_TABLE, $values);
	}

	/**
	 * @inheritdoc
	 */
	public function loadSettings(ilObjManualAssessment $obj)
	{
		if (ilObjManualAssessment::_exists($obj->getId(), false, 'mass')) {
			$obj_id = $obj->getId();
			assert('is_numeric($obj_id)');

			$sql = 	'SELECT content, record_template, file_required, event_time_place_required, superior_examinate, superior_view, grade_self, view_self'
					.'	FROM mass_settings WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
			if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilManualAssessmentSettings(
					$obj,
					$res["content"],
					$res["record_template"],
					(bool)$res["file_required"],
					$res["event_time_place_required"],
					(bool)$res["superior_examinate"],
					(bool)$res["superior_view"],
					(bool)$res["grade_self"],
					(bool)$res['view_self']
				);
			}
			throw new ilManualAssessmentException("$obj_id not in database");
		} else {
			return new ilManualAssessmentSettings($obj);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function updateSettings(ilManualAssessmentSettings $settings)
	{
		$where = array( "obj_id" => array("integer", $settings->getId()));

		$values = array
				( "content" => array("text", $settings->content())
				, "record_template" => array("text", $settings->recordTemplate())
				, "file_required" => array("integer", $settings->fileRequired())
				, "event_time_place_required" => array("integer", $settings->eventTimePlaceRequired())
				, "superior_examinate" => array("integer", $settings->superiorExaminate())
				, "superior_view" => array("integer", $settings->superiorView())
				, "grade_self" => array("integer", $settings->gradeSelf())
				, "view_self" => array("integer", $settings->viewSelf())
				);

		$this->db->update(self::MASS_SETTINGS_TABLE, $values, $where);
	}

	/**
	 * Load info-screen settings corresponding to obj
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @return	ilManualAssessmentSettings	$settings
	 */
	public function loadInfoSettings(ilObjManualAssessment $obj)
	{
		if (ilObjManualAssessment::_exists($obj->getId(), false, 'mass')) {
			$obj_id = $obj->getId();
			assert('is_numeric($obj_id)');

			$sql = "SELECT contact, responsibility, phone, mails, consultation_hours"
				  ." FROM ".self::MASS_SETTINGS_INFO_TABLE."\n"
				  ." WHERE obj_id = ".$this->db->quote($obj_id, 'integer');

			if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilManualAssessmentInfoSettings(
					$obj,
					$res["contact"],
					$res["responsibility"],
					$res['phone'],
					$res['mails'],
					$res['consultation_hours']
				);
			}
			throw new ilManualAssessmentException("$obj_id not in database");
		} else {
			return new ilManualAssessmentInfoSettings($obj);
		}
	}

	/**
	 * Update info-screen settings entry.
	 *
	 * @param	ilManualAssessmentSettings	$settings
	 */
	public function updateInfoSettings(ilManualAssessmentInfoSettings $settings)
	{
		$where = array("obj_id" => array("integer", $settings->id()));

		$values = array
				( "contact" => array("text", $settings->contact())
				, "responsibility" => array("text", $settings->responsibility())
				, "phone" => array("text", $settings->phone())
				, "mails" => array("text", $settings->mails())
				, "consultation_hours" => array("text", $settings->consultationHours())
				);

		$this->db->update(self::MASS_SETTINGS_INFO_TABLE, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteSettings(ilObjManualAssessment $obj)
	{
		$sql = 'DELETE FROM '.self::MASS_SETTINGS_TABLE.' WHERE obj_id = %s';
		$this->db->manipulateF($sql, array("integer"), array($obj->getId()));
		$sql = 'DELETE FROM '.self::MASS_SETTINGS_INFO_TABLE.' WHERE obj_id = %s';
		$this->db->manipulateF($sql, array("integer"), array($obj->getId()));
	}
}
