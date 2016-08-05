<?php
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");
require_once("Services/User/classes/class.ilObjUser.php");
require_once(__DIR__."/Actions.php");

class ilActions implements Actions {
	public function __construct() {
		global $ilDB;

		$this->gDB = $ilDB;
	}

	/**
	 * @inheritdoc
	 */
	public function getUserObj($user_id) {
		return new \ilObjUser($user_id);
	}

	/**
	 * @inheritdoc
	 */
	public function getExitedUserIds() {
		$ret = array();

		$exit_udf_field_id = $this->getFieldIdExitDate();

		$res = $this->gDB->query("SELECT ud.usr_id "
				   ."  FROM usr_data ud"
				   ."  JOIN udf_text udf "
				   ."    ON udf.usr_id = ud.usr_id"
				   ."   AND field_id = ".$this->gDB->quote($exit_udf_field_id, "integer")
				   ." WHERE active = 1 "
				   ."   AND udf.value < CURDATE()"
				   );

		while ($rec = $this->gDB->fetchAssoc($res)) {
			$ret[] = $rec["usr_id"];
		}

		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function getBookedCoursesFor($user_id) {
		$usr_utils = \gevUserUtils::getInstance($user_id);
		return $usr_utils->getBookedAndWaitingCourses();
	}

	/**
	 * @inheritdoc
	 */
	public function setUserToWBDRelease($user_id) {
		$wbd_utils = \gevWBD::getInstance($user_id);

		if($wbd_utils->getWBDTPType() == \gevWBD::WBD_TP_SERVICE) {
			$wbd_utils->setNextWBDAction(\gevWBD::USR_WBD_NEXT_ACTION_RELEASE);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getStartDateOf($crs_id) {
		return $this->getCourseUtils($crs_id)->getStartDate();
	}

	/**
	 * @inheritdoc
	 */
	public function cancelBookings($crs_id, $user_id) {
		$this->getCourseUtils($crs_id)->getBookings()->cancelWithoutCosts($user_id);
	}

	/**
	 * @inheritdoc
	 */
	public function sendMail($topic, $crs_id, $user_id) {
		$mails = new \gevCrsAutoMails($crs_id);
		$mails->send($topic, array($user_id));
	}

	/**
	 * @inheritdoc
	 */
	public function deassignOrgUnits($user_id) {
		foreach($this->getOrguTree()->getOrgUnitOfUser($user_id, 0, true) as $orgu_id) {
			$orgu_utils = $this->getOrUnitUtilsForObjId($orgu_id);
			$orgu_utils->deassignUser($user_id, "Mitarbeiter");
			$orgu_utils->deassignUser($user_id, "Vorgesetzter");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function assignUserToExitOrgu($user_id) {
		$exit_orgu_utils = $this->getOrUnitUtilsForObjId($this->getOrgUnitForExitUser());
		$exit_orgu_utils->assignUser($user_id, "Mitarbeiter");
	}

	/**
	 * @inheritdoc
	 */
	public function getNAsOf($user_id) {
		return $this->getNAUtils()->getNAsOf($user_id);
	}

	/**
	 * @inheritdoc
	 */
	public function moveNAToNoAdviserOrgUnit($na) {
		$na_no_adviser_orgu_utils = $this->getOrUnitUtilsForObjId($this->getNAPOUNoAdviserUnitId());
		$na_no_adviser_orgu_utils->assignUser($na, "Mitarbeiter");
	}

	/**
	 * @inheritdoc
	 */
	public function removeNAOrgUnitOf($user_id) {
		$this->getNAUtils()->removeNAOrgUnitOf($user_id);
	}

	/**
	 * @inheritdoc
	 */
	public function purgeEmptyNABaseChildren() {
		$na_base_utils = $this->getOrUnitUtilsForObjId($this->getGEVSettings()->getNAPOUBaseUnitId());

		$no_adviser_ref_id = \gevObjectUtils::getRefId($this->getGEVSettings()->getNAPOUNoAdviserUnitId());
		$template_ref_id = \gevObjectUtils::getRefId($this->getGEVSettings()->getNAPOUTemplateUnitId());

		$na_base_utils->purgeEmptyChildren(2, array($no_adviser_ref_id, $template_ref_id));
	}

	protected function getGEVSettings() {
		if($this->gev_settings === null) {
			$this->gev_settings = \gevSettings::getInstance();
		}

		return $this->gev_settings;
	}

	protected function getOrguTree() {
		if($this->orgu_tree === null) {
			$this->orgu_tree = \ilObjOrgUnitTree::_getInstance();
		}

		return $this->orgu_tree;
	}

	protected function getNAUtils() {
		if($this->na_utils === null) {
			$this->na_utils = \gevNAUtils::getInstance();
		}

		return $this->na_utils;
	}

	protected function getCourseUtils($crs_id) {
		return \gevCourseUtils::getInstance($crs_id);
	}

	protected function getFieldIdExitDate() {
		return $this->getUDFFieldId(\gevSettings::USR_UDF_EXIT_DATE);
	}

	protected function getUDFFieldId($field_name) {
		return $this->getGEVSettings()->getUDFFieldId($field_name);
	}

	protected function getOrgUnitForExitUser() {
		return $this->getGEVSettings()->getOrgUnitExited();
	}

	protected function getNAPOUNoAdviserUnitId() {
		return $this->getGEVSettings()->getNAPOUNoAdviserUnitId();
	}

	protected function getObjectIdFor($ref_id) {
		return \gevObjectUtils::getObjId($ref_id);
	}

	protected function getOrUnitUtilsForObjId($obj_id) {
		return \gevOrgUnitUtils::getInstance($obj_id);
	}

	protected function getOrgUnitUtilsForRefId($ref_id) {
		return $this->getOrUnitUtilsForObjId($this->getObjectIdFor($ref_id));
	}

}