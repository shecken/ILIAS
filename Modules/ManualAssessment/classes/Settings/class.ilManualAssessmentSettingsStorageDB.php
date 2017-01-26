<?php
/** 
 * A settings storage handler to write mass settings to db.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de> 
 */
require_once 'Modules/ManualAssessment/interfaces/Settings/interface.ilManualAssessmentSettingsStorage.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentInfoSettings.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
class ilManualAssessmentSettingsStorageDB implements ilManualAssessmentSettingsStorage {

	protected $db;
	public function __construct($db) {
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createSettings(ilManualAssessmentSettings $settings) {
		$sql = "INSERT INTO mass_settings (content,record_template,obj_id,event_time_place_required) VALUES (%s,%s,%s,%s)";
		$this->db->manipulateF($sql,array("text","text","integer","integer"),
			array($settings->content(),$settings->recordTemplate(),$settings->getId(), $settings->eventTimePlaceRequired()));
		$sql = "INSERT INTO mass_info_settings (obj_id) VALUES (%s)";
		$this->db->manipulateF($sql,array("integer"),array($settings->getId()));
	}

	/**
	 * @inheritdoc
	 */
	public function loadSettings(ilObjManualAssessment $obj) {
		if(ilObjManualAssessment::_exists($obj->getId(), false, 'mass')) {
			$obj_id = $obj->getId();
			assert('is_numeric($obj_id)');
			$sql = 'SELECT content, record_template, event_time_place_required FROM mass_settings WHERE obj_id = '.$this->db->quote($obj_id,'integer');
			if($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilManualAssessmentSettings($obj, $res["content"],$res["record_template"],$res["event_time_place_required"]);
			}
			throw new ilManualAssessmentException("$obj_id not in database");
		} else {
			return new ilManualAssessmentSettings($obj, ilManualAssessmentSettings::DEF_CONTENT, ilManualAssessmentSettings::DEF_RECORD_TEMPLATE,
												  ilManualAssessmentSettings::DEF_EVENT_TIME_PLACE_REQUIRED);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function updateSettings(ilManualAssessmentSettings $settings) {
		$sql = 'UPDATE mass_settings SET content = %s,record_template = %s, event_time_place_required = %s WHERE obj_id = %s';
		$this->db->manipulateF($sql,array("text","text","integer","integer"),
			array($settings->content(),$settings->recordTemplate(),$settings->eventTimePlaceRequired(), $settings->getId()));
	}


	/**
	 * Load info-screen settings corresponding to obj
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @return	ilManualAssessmentSettings	$settings
	 */
	public function loadInfoSettings(ilObjManualAssessment $obj) {
		if(ilObjManualAssessment::_exists($obj->getId(), false, 'mass')) {
			$obj_id = $obj->getId();
			assert('is_numeric($obj_id)');
			$sql = 	'SELECT contact, responsibility, phone, mails, consultation_hours'
					.'	FROM mass_info_settings WHERE obj_id = '.$this->db->quote($obj_id,'integer');
			if($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilManualAssessmentInfoSettings($obj,
					$res["contact"]
					,$res["responsibility"]
					,$res['phone']
					,$res['mails']
					,$res['consultation_hours']);
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
	public function updateInfoSettings(ilManualAssessmentInfoSettings $settings) {
		$sql = 	'UPDATE mass_info_settings SET '
				.'	contact = %s'
				.'	,responsibility = %s'
				.'	,phone = %s'
				.'	,mails = %s'
				.'	,consultation_hours = %s'
				.' WHERE obj_id = %s';
		$this->db->manipulateF($sql,array('text','text','text','text','text','integer'),
			array(	$settings->contact()
					,$settings->responsibility()
					,$settings->phone()
					,$settings->mails()
					,$settings->consultationHours()
					,$settings->id()));
	}

	/**
	 * @inheritdoc
	 */
	public function deleteSettings(ilObjManualAssessment $obj) {
		$sql = 'DELETE FROM mass_settings WHERE obj_id = %s';
		$this->db->manipulateF($sql,array("integer"),array($obj->getId()));
		$sql = 'DELETE FROM mass_info_settings WHERE obj_id = %s';
		$this->db->manipulateF($sql,array("integer"),array($obj->getId()));
	}
}