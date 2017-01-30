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

			$sql = 'SELECT content, record_template, file_required, event_time_place_required FROM mass_settings WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
			if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilManualAssessmentSettings($obj, $res["content"], $res["record_template"], (bool)$res["file_required"], $res["event_time_place_required"]);
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
				, "record_template" => array("integer", $settings->eventTimePlaceRequired())
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
			$sql = 	'SELECT contact, responsibility, phone, mails, consultation_hours'
					.'	FROM mass_info_settings WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
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
		$sql = 	'UPDATE mass_info_settings SET '
				.'	contact = %s'
				.'	,responsibility = %s'
				.'	,phone = %s'
				.'	,mails = %s'
				.'	,consultation_hours = %s'
				.' WHERE obj_id = %s';
		$this->db->manipulateF(
			$sql,
			array('text','text','text','text','text','integer'),
			array(	$settings->contact()
					,$settings->responsibility()
					,$settings->phone()
					,$settings->mails()
					,$settings->consultationHours()
			,
			$settings->id())
		);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteSettings(ilObjManualAssessment $obj)
	{
		$sql = 'DELETE FROM mass_settings WHERE obj_id = %s';
		$this->db->manipulateF($sql, array("integer"), array($obj->getId()));
		$sql = 'DELETE FROM mass_info_settings WHERE obj_id = %s';
		$this->db->manipulateF($sql, array("integer"), array($obj->getId()));
	}
}
