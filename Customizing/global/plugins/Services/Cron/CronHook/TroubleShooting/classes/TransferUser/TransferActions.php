<?php
/**
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/CourseBooking/classes/class.ilCourseBookings.php");

class TransferActions {
	protected $lUser;
	protected $eUser;
	protected $gDB;
	protected $today;
	protected $today_ts;
	protected $utils_lUser;
	protected $utils_eUser;

	public function __construct($db)
	{
		$this->gDB = $db;
	}

	/**
	* Sucht nach Kursen die lUser hat und die keinen Status besitzen
	* Der eUser darf auch keinen Status haben. Konflikt Serlbstlernkurse
	*
	* @param array 	$lUser_crs_infos
	* @param array 	$eUser_crs_infos
	* @param int 	$lUser_id
	* @param int 	$eUser_id
	*/
	public function getBlockers($lUser_crs_infos, $eUser_crs_infos)
	{
		$lUser_no_status = 	array_filter(
								array_map(
									function($v1) { if($v1["participation_status_level"] == gevUserUtils::NICHT_GESETZT) {return $v1["crs_id"];} }
									, $lUser_crs_infos)
								, function($v1) { return $v1 !== null;}
							);

		$eUser_status = 	array_filter(
								array_map(
									function($v1) { if($v1["participation_status_level"] != gevUserUtils::NICHT_GESETZT) {return $v1["crs_id"];} }
									, $eUser_crs_infos)
								, function($v1) { return $v1 !== null; }
							);

		return array_diff($lUser_no_status, $eUser_status);
	}

	/**
	* Ãndert die Kurse die NUR der lUser hat und an den einen Status gesetzt ist
	*
	* @param array 	$lUser_crs_infos
	* @param array 	$eUser_crs_infos
	* @param int 	$eUser_id
	*/
	public function changeCrsWhereLUserHasStatusAndEUserNot($lUser_crs_infos, $eUser_crs_infos, $eUser_id)
	{
		$status = $this->crsWhereLUserHasStatusAndEUserNot($lUser_crs_infos, $eUser_crs_infos);

		foreach ($status as $crs_info) {
			$this->changeUserToEUserForRow($crs_info["row_id"], $eUser_id);
			$this->moveBill($crs_info["crs_id"], $eUser_id);
		}
	}

	/**
	* Ãndert die Kurse die beide besitzern auf den hÃ¶heren Status
	*
	* Status Reihe nach Wertigkeit absteigend
	*
	* teilgenommen = 3
	* fehlt entschuldigt = 2
	* fehlt ohne Absage = 1
	* nicht gesetzt = 0
	* Sonstige = -1
	*
	* @param array 	$lUser_crs_infos
	* @param array 	$eUser_crs_infos
	* @param int 	$lUser_id
	* @param int 	$eUser_id
	*/
	public function changeCrsWhereStatusIsHigher($lUser_crs_infos, $eUser_crs_infos, $lUser_id, $eUser_id)
	{
		$higher_status = $this->crsWhereStatusIsHigher($lUser_crs_infos, $eUser_crs_infos);

		foreach ($higher_status as $key => $crs_info) {
			if($crs_info["usr_id"] == $lUser_id) {
				$this->changeUserToEUserForRow($crs_info["row_id"], $eUser_id);
				$this->setHistoricFor($crs_info["crs_id"], $eUser_id, $crs_info["row_id"]);
				$this->deleteBill($crs_info["crs_id"], $eUser_id);
				$this->moveBill($crs_info["crs_id"], $eUser_id);
			} else if($crs_info["usr_id"] == $eUser_id) {
				$this->setHistoricFor($crs_info["crs_id"], $lUser_id);
				$this->deleteBill($crs_info["crs_id"], $lUser_id);
			}
		}
	}

	/**
	* storno crs for User
	*
	* @param integer
	* @param array
	*/
	public function cancelCoursesFor($lUser_id, $eUser_id, $user_crs_infos)
	{
		foreach ($user_crs_infos as $crs_info) {
			$crs = new ilObjCourse($crs_info["crs_id"], false);
			$bookings = ilCourseBookings::getInstance($crs);
			$this->moveOvernights($lUser_id, $eUser_id, array($crs_info));
			$bookings->cancelWithoutCosts($lUser_id);
		}
	}

	/**
	* books crs if eUser is not booked on
	*
	* @param array
	* @param array
	* @param int 	$eUser_id
	*
	* @return array 	booked_crs_ids
	*/
	public function bookCoursesFor($canceld_crs_infos, $user_crs_infos, $eUser_id)
	{
		$canceld_crs_ids = $this->getOnlyCrsId($canceld_crs_infos);
		$user_crs_ids = $this->getOnlyCrsId($user_crs_infos);

		$to_book = array_diff($canceld_crs_ids, $user_crs_ids);

		foreach ($to_book as $crs_id) {
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$crs_utils->bookUser($eUser_id);
		}

		return array_merge($canceld_crs_infos, $user_crs_infos);
	}

	/**
	* deletes all OrguAssignemnts for lUser
	* moves lUser in Orgunit "Doppelte User"
	*/
	public function changeOrgUnitsForLUser($lUser_orgunits, $lUser_id)
	{
		foreach ($lUser_orgunits as $value) {
			$orgu_utils = gevOrgUnitUtils::getInstance($value["obj_id"]);
			$orgu_utils->deassignUser($lUser_id,"Mitarbeiter");
		}

		$orgu_utils = gevOrgUnitUtils::getInstance(gevSettings::getInstance()->getDuplicatedUserOrgUnitId());
		$orgu_utils->assignUser($lUser_id, "Mitarbeiter");
	}

	protected function crsWhereLUserHasStatusAndEUserNot($lUser_crs_infos, $eUser_crs_infos) {
		$lUser_status = array_filter(
			array_map(
				function($v1) { if($v1["participation_status_level"] > gevUserUtils::NICHT_GESETZT) {return $v1["crs_id"];} }
					, $lUser_crs_infos
			)
			, function($v1) { return $v1 !== null; }
		);

		$eUser_crs_ids = $this->getOnlyCrsId($eUser_crs_infos);

		$diffs = array_diff($lUser_status, $eUser_crs_ids);

		$callback_only_lUser = function($v1, $v2) { 
			if($v1["crs_id"] == $v2) {
				return $v1;
			}
		};

		$status = $this->compareCoursesByCallback($lUser_crs_infos, $diffs, $callback_only_lUser);

		return $status;
	}

	/**
	* Gibt ein array zurÃŒck gefilter nach der Funktion aus $callback
	*
	* @param array
	* @param array
	* @param function
	*
	* @return array
	*/
	protected function compareCoursesByCallback($lUser_crs_infos, $eUser_crs_infos, $callback)
	{
		$ret = array();

		foreach ($lUser_crs_infos as $key => $value1) {
			foreach ($eUser_crs_infos as $key => $value2) {
				$ret[] = $callback($value1,$value2);
			}
		}

		$ret = array_unique($ret, SORT_REGULAR);
		$ret = array_filter($ret, function($v1) { return $v1 !== null;});

		return $ret;
	}

	/**
	* Ãndert den hist historic 0 eintrag auf den eUser
	*
	* @param int 	$row_id
	* @param int 	$eUser_id
	*/
	protected function changeUserToEUserForRow($row_id, $eUser_id)
	{
		$query = "UPDATE hist_usercoursestatus\n"
				." set usr_id = ".$this->gDB->quote($eUser_id,"integer")."\n"
				." WHERE row_id = ".$this->gDB->quote($row_id,"integer");
		$this->gDB->manipulate($query);
	}

	/**
	* Verschiebt die Rechnung auf $user
	*
	* @param integer
	* @param integer
	*/
	protected function moveBill($crs_id, $user)
	{
		$query = "UPDATE bill\n"
				." set bill_usr_id = ".$this->gDB->quote($user,"integer")."\n"
				." WHERE bill_context_id = ".$this->gDB->quote($crs_id,"integer");
		$this->gDB->manipulate($query);
	}

	protected function crsWhereStatusIsHigher($lUser_crs_infos, $eUser_crs_infos)
	{
		$callback_higher_status = function($v1, $v2) {
			if($v1["crs_id"] == $v2["crs_id"]) {
				if($v1["participation_status"] != $v2["participation_status"]) {
					if($v1["participation_status_level"] > $v2["participation_status_level"]) {
						return $v1;
					} else {
						return $v2;
					}
				} else {
					return $v2;
				}
			}
		};

		return $this->compareCoursesByCallback($lUser_crs_infos, $eUser_crs_infos, $callback_higher_status);
	}

	/**
	* setzt den hist historic 0 eintrag vom $user auf hist historic 1
	*
	* @param integer
	* @param integer
	* @param integer
	*/
	protected function setHistoricFor($crs_id, $user, $exclude_row = null)
	{
		$query = "UPDATE hist_usercoursestatus\n"
				." set hist_historic = 1\n"
				." WHERE crs_id = ".$this->gDB->quote($crs_id,"integer")."\n"
				." AND hist_historic = 0\n"
				." AND usr_id = ".$this->gDB->quote($user,"integer");

		if($exclude_row) {
			$query .= " AND row_id != ".$this->gDB->quote($exclude_row,"integer");
		}
		$this->gDB->manipulate($query);
	}

	/**
	* löscht die Rechnung fÃŒr $user
	*
	* @param integer
	* @param integer
	*/
	protected function deleteBill($crs_id, $user)
	{
		$query = "DELETE FROM bill\n"
				." WHERE bill_usr_id = ".$this->gDB->quote($user,"integer")."\n"
				."    AND bill_context_id = ".$this->gDB->quote($crs_id,"integer")."\n";
		$this->gDB->manipulate($query);
	}

	/**
	* moves overnights vom lUser to eUser
	*
	* @param array
	*/
	protected function moveOvernights($lUser_id, $eUser_id, $overnights)
	{
		$overnights_crs_ids = array_unique($this->getOnlyCrsId($overnights));

		foreach ($overnights_crs_ids as $crs_id) {
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$crs_acco = $crs_utils->getAccomodations();

			$user_crs_acco = $crs_acco->getAccomodationsOfUsers(array($lUser_id,$eUser_id));

			if(array_key_exists($eUser_id, $user_crs_acco)) {
				$crs_acco->deleteAccomodations($lUser_id);
				continue;
			}

			$query = "UPDATE crs_acco\n"
					." SET user_id = ".$this->gDB->quote($eUser_id,"integer")."\n"
					." WHERE crs_id = ".$this->gDB->quote($crs_id,"integer");
			$this->gDB->manipulate($query);
		}
	}

	/**
	* gets only crs id out of crs_info arrays
	*
	* @return array
	*/
	protected function getOnlyCrsId($crs_infos)
	{
		return 	array_filter(
					array_map(
						function($v1) { return $v1["crs_id"]; }
						, $crs_infos)
					, function($v1) { return $v1 !== null; }
				);
	}
}