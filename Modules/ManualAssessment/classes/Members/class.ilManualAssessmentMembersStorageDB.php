<?php
require_once 'Modules/ManualAssessment/interfaces/Members/interface.ilManualAssessmentMembersStorage.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMember.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
/**
 * Store member infos to DB
 * @inheritdoc
 */
class ilManualAssessmentMembersStorageDB implements ilManualAssessmentMembersStorage
{
	const MEMBERS_TABLE = "mass_members";

	protected $db;

	public function __construct($ilDB)
	{
		$this->db = $ilDB;
	}

	/**
	 * @inheritdoc
	 */
	public function loadMembers(ilObjManualAssessment $obj)
	{
		$members = new ilManualAssessmentMembers($obj);
		$obj_id = $obj->getId();
		$sql = $this->loadMembersQuery($obj_id);
		$res = $this->db->query($sql);
		while ($rec = $this->db->fetchAssoc($res)) {
			$members = $members->withAdditionalRecord($rec);
		}
		return $members;
	}

	/**
	 * @inheritdoc
	 */
	public function loadMember(ilObjManualAssessment $obj, ilObjUser $usr)
	{
		$obj_id = $obj->getId();
		$usr_id = $usr->getId();
		$sql = 'SELECT massme.*'
				.' FROM mass_members massme'
				.'	JOIN usr_data usr ON massme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON massme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer')
				.'		AND massme.usr_id = '.$this->db->quote($usr_id, 'integer');
		$rec = $this->db->fetchAssoc($this->db->query($sql));
		if ($rec) {
			$member = new ilManualAssessmentMember($obj, $usr, $rec);
			return $member;
		} else {
			throw new ilManualAssessmentException("invalid usr-obj combination");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function updateMember(ilManualAssessmentMember $member)
	{
		$where = array("obj_id" => array("integer", $member->assessmentId())
					 , "usr_id" => array("integer", $member->id())
				);

		$values = array(ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS => array("text", $member->LPStatus())
					  , ilManualAssessmentMembers::FIELD_EXAMINER_ID => array("integer", $member->examinerId())
					  , ilManualAssessmentMembers::FIELD_RECORD => array("text", $member->record())
					  , ilManualAssessmentMembers::FIELD_INTERNAL_NOTE => array("text", $member->internalNote())
					  , ilManualAssessmentMembers::FIELD_PLACE => array("text", $member->place())
					  , ilManualAssessmentMembers::FIELD_EVENTTIME => array("integer", $member->eventTime()->get(IL_CAL_UNIX))
					  , ilManualAssessmentMembers::FIELD_NOTIFY => array("integer", $member->notify() ? 1 : 0)
					  , ilManualAssessmentMembers::FIELD_FINALIZED => array("integer", $member->finalized() ? 1 : 0)
					  , ilManualAssessmentMembers::FIELD_NOTIFICATION_TS => array("integer", $member->notificationTS())
					  , ilManualAssessmentMembers::FIELD_FILE_NAME => array("text", $member->fileName())
				);

		$this->db->update(self::MEMBERS_TABLE, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteMembers(ilObjManualAssessment $obj)
	{
		$sql = "DELETE FROM mass_members WHERE obj_id = ".$this->db->quote($obj->getId(), 'integer');
		$this->db->manipulate($sql);
	}

	/**
	 * @inheritdoc
	 */
	protected function loadMembersQuery($obj_id)
	{
		return 'SELECT ex.firstname as '.ilManualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME
				.'	, ex.lastname as '.ilManualAssessmentMembers::FIELD_EXAMINER_LASTNAME
				.'	,usr.firstname as '.ilManualAssessmentMembers::FIELD_FIRSTNAME
				.'	,usr.lastname as '.ilManualAssessmentMembers::FIELD_LASTNAME
				.'	,usr.login as '.ilManualAssessmentMembers::FIELD_LOGIN
				.'	,massme.*'
				.' FROM mass_members massme'
				.'	JOIN usr_data usr ON massme.usr_id = usr.usr_id'
				.'	LEFT JOIN usr_data ex ON massme.examiner_id = ex.usr_id'
				.'	WHERE obj_id = '.$this->db->quote($obj_id, 'integer');
	}

	/**
	 * @inheritdoc
	 */
	public function insertMembersRecord(ilObjManualAssessment $mass, array $record)
	{
		$values = array("obj_id" => array("integer", $mass->getId())
					  , "usr_id" => array("integer", $record[ilManualAssessmentMembers::FIELD_USR_ID])
					  , ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS => array("text", $record[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS])
					  , ilManualAssessmentMembers::FIELD_EXAMINER_ID => array("integer", $member->examinerId())
					  , ilManualAssessmentMembers::FIELD_RECORD => array("text", $record[ilManualAssessmentMembers::FIELD_RECORD])
					  , ilManualAssessmentMembers::FIELD_INTERNAL_NOTE => array("text", $member->internalNote())
					  , ilManualAssessmentMembers::FIELD_PLACE => array("text", $record[ilManualAssessmentMembers::FIELD_PLACE])
					  , ilManualAssessmentMembers::FIELD_EVENTTIME => array("integer", $record[ilManualAssessmentMembers::FIELD_EVENTTIME])
					  , ilManualAssessmentMembers::FIELD_NOTIFY => array("integer", 0)
					  , ilManualAssessmentMembers::FIELD_FINALIZED => array("integer", 0)
					  , ilManualAssessmentMembers::FIELD_NOTIFICATION_TS => array("integer", -1)
					  , ilManualAssessmentMembers::FIELD_FILE_NAME => array("text", $member->fileName())
				);

		$this->db->insert(self::MEMBERS_TABLE, $values);
	}

	/**
	 * @inheritdoc
	 */
	public function removeMembersRecord(ilObjManualAssessment $mass, array $record)
	{
		$sql = 'DELETE FROM mass_members'
				.'	WHERE obj_id = '.$this->db->quote($mass->getId(), 'integer')
				.'		AND usr_id = '.$this->db->quote($record[ilManualAssessmentMembers::FIELD_USR_ID], 'integer');
		$this->db->manipulate($sql);
	}
}
