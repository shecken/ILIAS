<?php

namespace CaT\Plugins\TalentAssessment\Settings;
use CaT\Plugins\CareerGoal\Settings as CareerGoal;

require_once("./Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

class ilDB implements DB
{
	const PLUGIN_TABLE = "rep_obj_xtas";
	const USR_TABLE = "usr_data";
	const CAREER_GOAL_TABLE = "rep_obj_xcgo";

	public function __construct($db, $user, CareerGoal\ilDB $career_goal_db)
	{
		$this->db = $db;
		$this->user = $user;
		$this->career_goal_db = $career_goal_db;
	}

	/**
	 * @inheritdoc
	 */
	public function install()
	{
		$this->createTable();
		$this->addColumns();
	}

	protected function createTable()
	{
		if (!$this->getDB()->tableExists(self::PLUGIN_TABLE)) {
			$fields =
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'state' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'career_goal_id' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> true
					),
					'username' => array(
						'type' 		=> 'text',
						'length'	=> 80,
						'notnull' 	=> true
					),
					'start_date' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'end_date' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'venue' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> false
					),
					'org_unit' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> false
					),
					'started' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> false
					),
					'lowmark' => array(
						'type' 		=> 'float',
						'notnull' 	=> false
					),
					'should_specification' => array(
						'type' 		=> 'float',
						'notnull' 	=> false
					),
					'potential' => array(
						'type' 		=> 'float',
						'notnull' 	=> false
					),
					'result_comment' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'last_change_user' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::PLUGIN_TABLE, $fields);
			$this->getDB()->addPrimaryKey(self::PLUGIN_TABLE, array("obj_id"));
		}
	}

	protected function addColumns()
	{
		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "default_text_failed")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "default_text_failed", array(
						'type' 		=> 'clob',
						'notnull' 	=> false));
		}

		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "default_text_partial")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "default_text_partial", array(
						'type' 		=> 'clob',
						'notnull' 	=> false));
		}

		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "default_text_success")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "default_text_success", array(
						'type' 		=> 'clob',
						'notnull' 	=> false));
		}

		if (!$this->getDB()->tableColumnExists(self::PLUGIN_TABLE, "report_title")) {
			$this->getDB()->addTableColumn(self::PLUGIN_TABLE, "report_title", array(
						'type' 		=> 'text',
						'length' 	=> 255,
						'notnull' 	=> false));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function create($obj_id, $state, $career_goal_id, $username, $firstname, $lastname, $email, $start_date, $end_date, $venue, $org_unit, $started, $lowmark, $should_specification, $potential, $result_comment, $default_text_failed, $default_text_partial, $default_text_success, $report_title)
	{
		$talent_assessment = new TalentAssessment($obj_id, $state, $career_goal_id, $username, $firstname, $lastname, $email, $start_date, $end_date, $venue, $org_unit, $started, $lowmark, $should_specification, $potential, $result_comment, $default_text_failed, $default_text_partial, $default_text_success, $report_title);

		$values = array
				( "obj_id" => array("integer", $talent_assessment->getObjId())
				, "state" => array("integer", $talent_assessment->getState())
				, "career_goal_id" => array("integer", $talent_assessment->getCareerGoalId())
				, "username" => array("text", $talent_assessment->getUsername())
				, "start_date" => array("text", $talent_assessment->getStartDate()->get(IL_CAL_DATETIME))
				, "end_date" => array("text", $talent_assessment->getEndDate()->get(IL_CAL_DATETIME))
				, "venue" => array("text", $talent_assessment->getVenue())
				, "org_unit" => array("text", $talent_assessment->getOrgUnit())
				, "started" => array("integer", $talent_assessment->getStarted())
				, "lowmark" => array("float", $talent_assessment->getLowmark())
				, "should_specification" => array("float", $talent_assessment->getShouldspecification())
				, "potential" => array("float", $talent_assessment->getPotential())
				, "result_comment" => array("text", $talent_assessment->getResultComment())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				, "default_text_failed" => array("text", $talent_assessment->getDefaultTextFailed())
				, "default_text_partial" => array("text", $talent_assessment->getDefaultTextPartial())
				, "default_text_success" => array("text", $talent_assessment->getDefaultTextSuccess())
				, "report_title" => array("text", $talent_assessment->getReportTitle())
				);
		$this->getDB()->insert(self::PLUGIN_TABLE, $values);

		return $talent_assessment;
	}

	/**
	 * updates talent assessment entries
	 *
	 * @param 	TalentAssessment 		$talent_assessment
	 */
	public function update(TalentAssessment $talent_assessment)
	{
		$values = array
				( "state" => array("integer", $talent_assessment->getState())
				, "career_goal_id" => array("integer", $talent_assessment->getCareerGoalId())
				, "username" => array("text", $talent_assessment->getUsername())
				, "start_date" => array("text", $talent_assessment->getStartDate()->get(IL_CAL_DATETIME))
				, "end_date" => array("text", $talent_assessment->getEndDate()->get(IL_CAL_DATETIME))
				, "venue" => array("text", $talent_assessment->getVenue())
				, "org_unit" => array("text", $talent_assessment->getOrgUnit())
				, "started" => array("integer", $talent_assessment->getStarted())
				, "lowmark" => array("float", $talent_assessment->getLowmark())
				, "should_specification" => array("float", $talent_assessment->getShouldspecification())
				, "potential" => array("float", $talent_assessment->getPotential())
				, "result_comment" => array("text", $talent_assessment->getResultComment())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				, "default_text_failed" => array("text", $talent_assessment->getDefaultTextFailed())
				, "default_text_partial" => array("text", $talent_assessment->getDefaultTextPartial())
				, "default_text_success" => array("text", $talent_assessment->getDefaultTextSuccess())
				, "report_title" => array("text",$talent_assessment->getReportTitle())
				);

		$where = array
				( "obj_id" => array("integer", $talent_assessment->getObjId())
				);

		$this->getDB()->update(self::PLUGIN_TABLE, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id)
	{
		$delete = "DELETE FROM ".self::PLUGIN_TABLE."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$this->getDB()->manipulate($delete);
	}

	/**
	 * @inheritdoc
	 */
	public function select($obj_id)
	{
		$select = "SELECT A.state, A.career_goal_id, A.username, A.start_date, A.end_date, A.venue, A.org_unit\n"
				.", A.started, A.lowmark, A.should_specification, A.potential, A.result_comment\n"
				.", A.default_text_failed, A.default_text_partial, A.default_text_success, A.report_title\n"
				.", B.firstname, B.lastname, B.email\n"
				." FROM ".self::PLUGIN_TABLE." A\n"
				." LEFT JOIN ".self::USR_TABLE." B\n"
				."     ON A.username = B.login"
				." WHERE A.obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		if (empty($row)) {
			throw new \InvalidArgumentException("Invalid id '$obj_id' for TalentAssessment-object");
		}

		$start_date = new \iLDateTime($row["start_date"], IL_CAL_DATETIME);
		$end_date = new \iLDateTime($row["end_date"], IL_CAL_DATETIME);

		$talent_assessment = new TalentAssessment((int)$obj_id, (int)$row["state"], (int)$row["career_goal_id"], $row["username"], $row["firstname"] ? $row["firstname"] : "", $row["lastname"] ? $row["lastname"] : "", $row["email"] ? $row["email"] : "", $start_date, $end_date, (int)$row["venue"], (int)$row["org_unit"], (bool)$row["started"], (float)$row["lowmark"], (float)$row["should_specification"], (float)$row["potential"], $row["result_comment"] ? $row["result_comment"] : "", $row["default_text_failed"] ? $row["default_text_failed"] : "", $row["default_text_partial"] ? $row["default_text_partial"] : "", $row["default_text_success"] ? $row["default_text_success"] : "", $row['report_title'] ? $row['report_title'] : '');

		return $talent_assessment;
	}

	public function cloneTalentAssessment($target_id, TalentAssessment $talent_assessment)
	{
		$values = array
				( "obj_id" => array("integer", (int)$target_id)
				, "state" => array("integer", TalentAssessment::IN_PROGRESS)
				, "career_goal_id" => array("integer", $talent_assessment->getCareerGoalId())
				, "username" => array("text", $talent_assessment->getUsername())
				, "start_date" => array("text", $talent_assessment->getStartDate()->get(IL_CAL_DATETIME))
				, "end_date" => array("text", $talent_assessment->getEndDate()->get(IL_CAL_DATETIME))
				, "venue" => array("text", $talent_assessment->getVenue())
				, "org_unit" => array("text", $talent_assessment->getOrgUnit())
				, "started" => array("integer", false)
				, "lowmark" => array("float", $talent_assessment->getLowmark())
				, "should_specification" => array("float", $talent_assessment->getShouldspecification())
				, "potential" => array("float", 0.0)
				, "result_comment" => array("text", "")
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				, "default_text_failed" => array("text", $talent_assessment->getDefaultTextFailed())
				, "default_text_partial" => array("text", $talent_assessment->getDefaultTextPartial())
				, "default_text_success" => array("text", $talent_assessment->getDefaultTextSuccess())
				, "report_title" => array("text", $talent_assessment->getReportTitle())
				);
		$this->getDB()->insert(self::PLUGIN_TABLE, $values);

		$new_talent_assessment = $this->select((int)$target_id);

		return $new_talent_assessment;
	}

	public function isStarted($obj_id)
	{
		$select = "SELECT started\n"
				." FROM ".self::PLUGIN_TABLE."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		return (bool)$row["started"];
	}

	/**
	 * @inheritdoc
	 */
	public function getCareerGoalsOptions()
	{
		$ret = array();

		$select = "SELECT obj.obj_id, obj.title\n"
				." FROM object_data obj\n"
				." JOIN object_reference ref\n"
				."   ON obj.obj_id = ref.obj_id\n"
				." WHERE obj.type = 'xcgo'\n"
				."   AND ref.deleted IS NULL";

		$res = $this->getDB()->query($select);

		while ($row = $this->getDB()->fetchAssoc($res)) {
			$ret[(int)$row["obj_id"]] = $row["title"];
		}

		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function getVenueOptions()
	{
		return \gevOrgUnitUtils::getVenueNames();
	}

	/**
	 * @inheritdoc
	 */
	public function getOrgUnitOptions()
	{
		$evg_id = \gevOrgUnitUtils::getEVGOrgUnitRefId();
		$org_unit_utils = \gevOrgUnitUtils::getAllChildren(array($evg_id));
		$evg_obj_id = \ilObject::_lookupObjId($evg_id);
		$evg_title = \ilObject::_lookupTitle($evg_obj_id);

		$ret = array($evg_obj_id => $evg_title);
		foreach ($org_unit_utils as $key => $value) {
			$ret[$value["obj_id"]] = \ilObject::_lookupTitle($value["obj_id"]);
		}

		return $ret;
	}

	public function getAllObservator($role_name)
	{
		$select = "SELECT usr_id, CONCAT(firstname, ' ', lastname) as name\n"
				 ." FROM usr_data\n"
				 ." WHERE usr_id IN\n"
				 ."     (SELECT DISTINCT usr_id\n"
				 ."      FROM rbac_ua rua\n"
				 ."      JOIN object_data od\n"
				 ."          ON rua.rol_id = od.obj_id\n"
				 ."      WHERE od.title LIKE ".$this->db->quote($role_name."%", "text").")";

		$res = $this->db->query($select);
		$ret = array();
		while ($row = $this->db->fetchAssoc($res)) {
			$ret[(int)$row["usr_id"]] = $row["name"];
		}

		return $ret;
	}

	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}

	/**
	 * returns user entries for all default result texts
	 *
	 * @param int 	$career_goal_id
	 *
	 * @return array
	 */
	public function getCareerGoalDefaultText($career_goal_id)
	{
		return $this->career_goal_db->getCareerGoalDefaultText($career_goal_id);
	}
}
