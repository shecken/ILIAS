<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of WBD DataCollector
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDTestDataCollector implements WBDDataCollector
{

	protected $gDB;
	protected $gAppEventHandler;
	protected $requests;
	protected $error_statement;
	protected $storno_rows;
	protected $abfrage_usr_ids;
	protected $gutberaten_id;
	protected $wbd_booking_id;

	const EMPTY_COLUMN_VALUE = "-empty-";
	const EMPTY_DATE_TEXT = "0000-00-00";

	const WBD_NO_SERVICE 		= "0 - kein Service";
	const WBD_EDU_PROVIDER		= "1 - Bildungsdienstleister";
	const WBD_TP_BASIS			= "2 - TP-Basis";
	const WBD_TP_SERVICE		= "3 - TP-Service";

	const TEST_USER_ID = 6;

	public function __construct($lms_folder)
	{
		chdir($lms_folder);
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestKontoAufnahme.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestKontoTransferfaehig.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestKontoAenderung.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestKontoErstanlage.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestBildungAbfrage.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestBildungszeitMeldung.php");
		require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequestBildungszeitStorno.php");
		require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
		require_once("Services/GEV/WBD/classes/class.gevWBD.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/UserCourseStatusHistorizing/classes/class.ilUserCourseStatusHistorizing.php");


		require_once "./Services/Context/classes/class.ilContext.php";
		ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
		require_once("./Services/Init/classes/class.ilInitialisation.php");
		ilInitialisation::initILIAS();

		global $ilDB, $ilAppEventHandler, $ilLog;
		$this->gDB =  $ilDB;
		$this->gAppEventHandler = $ilAppEventHandler;
		$this->gLog = $ilLog;

		$this->prepareErrorStatement();

		$this->requests = array();

		$this->stornoCounter = 0;
		$this->storno_rows = null;
		$this->abfrage_usr_ids = null;
	}

	/**********************************
	*
	* CREATE LISTS
	*
	**********************************/
	/**
	* creates a list of users to register in WBD
	*/
	public function createNewUserList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createNewUserList: Can't build new list. Still records left in the old one.");
		}

		$this->requests = $this->_createNewUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestKontoErstanlage[]
	 */
	protected function _createNewUserList($db)
	{
		$returns = array();
		$res = $this->getTestDataNewUser();

		while ($rec = array_shift($res)) {
			$wbd = $this->getWBDInstance($rec['user_id']);

			$checks_to_release = array();
			switch ($rec["next_wbd_action"]) {
				case gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE:
					$rec["wbd_type"] = self::WBD_TP_SERVICE;
					$checks_to_release = $wbd->shouldBeRegisteredAsNewTPServiceChecks();
					break;
				case gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS:
					$rec["wbd_type"] = self::WBD_TP_BASIS;
					$checks_to_release = $wbd->shouldBeRegisteredAsNewTPBasis();
					break;
			}

			$failed_checks = $this->performPreliminaryChecks($checks_to_release, $wbd);

			if (count($failed_checks) == 0) {
				$rec["address_type"] = "geschäftlich";
				$rec["info_via_mail"] = false;
				$rec["send_data"] = true;
				$rec["data_secure"] = true;
				$rec["country"] = "D";
				$rec["degree"] = "";
				$rec["address_info"] = "";

				$object = gevWBDRequestKontoErstanlage::getInstance($rec);
				if (is_array($object)) {
					foreach ($object as $error) {
						$this->error($error);
					}
					continue;
				}
				$returns[] = $object;
			} else {
				foreach ($failed_checks as $key => $value) {
					$error = new gevWBDError($value->message(), "pre", "new_user", $rec["user_id"], $rec["row_id"]);
					$this->error($error);
				}
			}
		}

		return $returns;
	}

	/**
	* creates the list of users to update in WBD
	*
	*/
	public function createUpdateUserList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createUpdateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createUpdateUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestKontoAenderung[]
	 */
	protected function _createUpdateUserList($db)
	{
		$returns = array();
		$res = $this->getTestDataUpdateUser();

		while ($rec = array_shift($res)) {
			$rec["address_type"] = "geschäftlich";
			$rec["info_via_mail"] = false;
			$rec["country"] = "D";
			$rec["degree"] = "";
			$rec["address_info"] = "";

			$object = gevWBDRequestKontoAenderung::getInstance($rec);
			if (is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$returns[] = $object;
		}
		return $returns;
	}

	/**
	* creates the list of users to release in WBD
	*
	*/
	public function createReleaseUserList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createReleaseUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createReleaseUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestKontoTransferfaehig[]
	 */
	protected function _createReleaseUserList($db)
	{
		$returns = array();
		$res = $this->getTestDataReleaseUser();

		while ($rec = array_shift($res)) {
			$wbd = $this->getWBDInstance($rec['user_id']);

			$checks_to_release = $wbd->shouldBeReleasedChecks();
			$failed_checks = $this->performPreliminaryChecks($checks_to_release, $wbd);

			if (count($failed_checks) == 0) {
				$object = gevWBDRequestKontoTransferfaehig::getInstance($rec);
				if (is_array($object)) {
					foreach ($object as $error) {
						$this->error($error);
					}
					continue;
				}
				$returns[] = $object;
			} else {
				foreach ($failed_checks as $key => $value) {
					$error = new gevWBDError($value->message(), "pre", "release_user", $rec["user_id"], $rec["row_id"]);
					$this->error($error);
				}
			}
		}

		return $returns;
	}

	/**
	* creates the list of users to gather for WBD Service
	*
	*/
	public function createAffiliateUserList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createAffiliateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createAffiliateUserList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestKontoAufnahme[]
	 */
	protected function _createAffiliateUserList($db)
	{
		$returns = array();
		$res = $this->getTestDataAffiliateUser();

		while ($rec = array_shift($res)) {
			$wbd = $this->getWBDInstance($rec['user_id']);
			$checks_to_release = $wbd->shouldBeAffiliateAsTPServiceChecks();
			$failed_checks = $this->performPreliminaryChecks($checks_to_release, $wbd);

			if (count($failed_checks) == 0) {
				$object = gevWBDRequestKontoAufnahme::getInstance($rec);
				if (is_array($object)) {
					foreach ($object as $error) {
						$this->error($error);
					}
					continue;
				}
				$returns[] = $object;
			} else {
				foreach ($failed_checks as $key => $value) {
					$error = new gevWBDError($value->message(), "pre", "affiliate_user", $rec["user_id"], $rec["row_id"]);
					$this->error($error);
				}
			}
		}

		return $returns;
	}

	/**
	* creates the list of new WP reports
	*
	*/
	public function createNewEduRecordList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createAffiliateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createNewEduRecordList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestBildungszeitMeldung[]
	 */
	protected function _createNewEduRecordList($db)
	{
		$returns = array();
		$res = $this->getTestDataNewEduRecord();

		while ($rec = $db->fetchAssoc($res)) {
			$object = gevWBDRequestBildungszeitMeldung::getInstance($rec);
			if (is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$returns[] = $object;
		}

		return $returns;
	}

	/**
	* creates the list of storno reports
	*
	*/
	public function createStornoRecordList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createAffiliateUserList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createStornoRecordList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestBildungszeitStorno[]
	 */
	protected function _createStornoRecordList($db)
	{
		$returns = array();
		$res = $this->getTestDataStorneEduRecord();

		while ($rec = $db->fetchAssoc($res)) {
			$object = gevWBDRequestBildungszeitStorno::getInstance($rec);
			if (is_array($object)) {
				foreach ($object as $error) {
					$this->error($error);
				}
				continue;
			}
			$returns[] = $object;
		}

		return $returns;
	}

	/**
	* creates the list of changed edu record
	*
	*/
	public function createUpdateEduRecordList()
	{
		//TODO
	}

	/**
	* creates the list of user records
	*
	*/
	public function createWPAbfrageRecordList()
	{
		if (!empty($this->requests)) {
			throw new LogicException("gevWBDDataCollector::createWPAbfrageRecordList: Can't build new list. Still records left.");
		}

		$this->requests = $this->_createWPAbfrageRecordList($this->gDB);
	}

	/**
	 * @param	ilDB 	$db
	 * @return  gevWBDRequestBildungAbfrage[]
	 */
	protected function _createWPAbfrageRecordList($db)
	{
		$returns = array();
		$res = $this->getTestDataWPAbfrage();

		while ($rec = array_shift($res)) {
			if (($counter + $start) % $use_every === 0) {
				$rec["certification_period"] = "Selektiert nicht stornierte Weiterbildungsmaßnahmen aus der aktuelle Zertifizierungsperiode.";

				$object = gevWBDRequestBildungAbfrage::getInstance($rec);
				if (is_array($object)) {
					foreach ($object as $error) {
						$this->gLog->write($error);
					}
					continue;
				}
				$returns[] = $object;
			}

			$counter++;
		}

		return $returns;
	}

	/**********************************
	*
	* SQL STATEMENTS
	*
	**********************************/
	/**
	*/
	protected function getTestDataNewUser()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 100;
		$data["user_id"] = self::TEST_USER_ID;
		$data["gender"] = "m";
		$data["email"] = "stefan.hecken@concepts-and-training.de";
		$data["wbd_email"] = "stefan.hecken@concepts-and-training.de";
		$data["mobile_phone_nr"] = "0049 123 9800608";
		$data["birthday"] =  "1981-06-12";
		$data["lastname"] = "Eins";
		$data["firstname"] = "Testuser";
		$data["city"] = "Köln";
		$data["next_wbd_action"] = "1 - Erstanlage TP Service";
		$data["zipcode"] = "50969";
		$data["phone_nr"] = "0049 123 9800608";
		$data["wbd_agent_status"] = "Makler";
		$data["wbd_type"] = "3 - TP-Service";
		$data["street"] = "Teststr 5";

		$ret[] = $data;
		return $ret;
	}

	/**
	*/
	protected function getTestDataUpdateUser()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 101;
		$data["user_id"] = self::TEST_USER_ID;
		$data["gender"] = "m";
		$data["email"] = "stefan.hecken@concepts-and-training.de";
		$data["wbd_email"] = "stefan.hecken@concepts-and-training.de";
		$data["mobile_phone_nr"] = "0049 123 9800708";
		$data["birthday"] = "1981-06-12";
		$data["lastname"] = "Eins";
		$data["firstname"] = "Testuser";
		$data["city"] = "Köln";
		$data["zipcode"] = "50969";
		$data["phone_nr"] = "0049 123 9800708";
		$data["wbd_agent_status"] = "Makler";
		$data["wbd_type"] = "3 - TP-Service";
		$data["street"] = "Teststr 5";
		$data["bwv_id"] = $this->guteberaten_id;

		$ret[] = $data;
		return $ret;
	}

	/**
	*/
	protected function getTestDataReleaseUser()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 102;
		$data["user_id"] = self::TEST_USER_ID;
		$data["email"] = "stefan.hecken@concepts-and-training.de";
		$data["mobile_phone_nr"] = "0049 123 9800708";
		$data["bwv_id"] = $this->guteberaten_id;

		$ret[] = $data;
		return $ret;
	}

	/**
	*/
	protected function getTestDataAffiliateUser()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 103;
		$data["user_id"] = self::TEST_USER_ID;
		$data["email"] = "stefan.hecken@concepts-and-training.de";
		$data["mobile_phone_nr"] = "0049 123 9800708";
		$data["birthday"] = "1981-06-12";
		$data["bwv_id"] = $this->guteberaten_id;
		$data["lastname"] = "Eins";
		$data["firstname"] = "Testuser";

		$ret[] = $data;
		return $ret;
	}

	/**
	*/
	protected function getTestDataNewEduRecord()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 104;
		$data["user_id"] = self::TEST_USER_ID;
		$data["begin_date"] = "2018-09-10T08:00:00+01:00";
		$data["end_date"] = "2018-09-10T16:00:00+01:00";
		$data["credit_points"] = 32;
		$data["type"] = "Präsenztraining";
		$data["wbd_topic"] = "Privat-Vorsorge-Lebens-/Rentenversicherung";
		$data["crs_id"] = 20;
		$data["title"] = "Ein Testkurs";
		$data["bwv_id"] = $this->gutberaten_id;
		$data["begin_of_certification"] = date("Y-m-d");

		$data["learning_time"] = $data["credit_points"] * 15;

		$ret[] = $data;
		return $ret;
	}

	/**
	*/
	protected function getTestDataStorneEduRecord()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 105;
		$data["wbd_booking_id"] = $this->wbd_booking_id;
		$data["user_id"] = self::TEST_USER_ID;
		$data["bwv_id"] = $this->gutberaten_id;

		$ret[] = $data;
		return $ret;
	}

	/**
	*/
	protected function getTestDataWPAbfrage()
	{
		$ret = array();

		$data = array();
		$data["row_id"] = 106;
		$data["user_id"] = self::TEST_USER_ID;
		$data["bwv_id"] = $this->gutberaten_id;

		$ret[] = $data;
		return $ret;
	}

	/**********************************
	*
	* SUCCESS CALLBACKS
	*
	**********************************/
	/**
	* callback public function if registration was successfull
	*
	* @param array $success_data
	*/
	public function successNewUser(gevWBDSuccessKontoErstanlage $success_data)
	{
		$this->gutberaten_id = $success_data->agentId();
		echo "User created: ".$this->gutberaten_id.PHP_EOL;
	}

	/**
	* callback public function if update was successfull
	*
	* @param gevWBDSuccessKontoAenderung $success_data
	*/
	public function successUpdateUser(gevWBDSuccessKontoAenderung $success_data)
	{
		echo "User updatet: ".$this->gutberaten_id.PHP_EOL;
	}

	/**
	* callback public function if release was successfull
	*
	* @param gevWBDSuccessVermitVerwaltungTransferfaehig $success_data
	*/
	public function successReleaseUser(gevWBDRequestKontoTransferfaehig $success_data)
	{
		echo "User released: ".$this->gutberaten_id.PHP_EOL;
	}

	/**
	* callback public function if affiliate was successfull
	*
	* @param array $success_data
	*/
	public function successAffiliateUser(gevWBDRequestKontoAufnahme $success_data)
	{
		echo "User affiliated: ".$this->gutberaten_id.PHP_EOL;
	}

	/**
	* callback public function if report was successfull
	*
	* @param gevWBDSuccessBildungszeitMeldung $success_data
	*/
	public function successNewEduRecord(gevWBDSuccessBildungszeitMeldung $success_data)
	{
		$this->wbd_booking_id = $success_data->wbdBookingId();
		echo "Course booked: ".$this->wbd_booking_id.PHP_EOL;
	}

	/**
	* callback public function if report was successfull
	*
	* @param gevWBDSuccessBildungszeitStorno $success_data
	*/
	public function successStornoRecord(gevWBDSuccessBildungszeitStorno $success_data)
	{
		echo "Course canceld: ".$this->wbd_booking_id.PHP_EOL;
	}

	/**
	* callback public function if report was successfull
	*
	* @param array $success_data
	*/
	public function successUpdateEduRecord($success_data)
	{
		//TODO
	}

	/**
	* callback public function if there are any WP reports for the user
	* creates new courses id necessary
	*
	* @param gevWBDSuccessBildungAbfrage $success_data
	*/
	public function successWPAbfrageRecord(gevWBDSuccessBildungAbfrage $success_data)
	{

		$import_course_data = $success_data->importCourseData();
		$import_course = array_shift($import_course_data);

		if($this->wbd_booking_id == $import_course->wbdBookingId()
			&& self::TEST_USER_ID == $success_data->userId()
		) {
			echo "Import worked for: ".$this->wbd_booking_id." user_id: ".$success_data->userId().PHP_EOL;
		}
	}

	/**********************************
	*
	* ERROR CALLBACK
	*
	**********************************/
	/**
	* callback public function for every error
	*
	* @param array $error_data
	*/
	public function error(gevWBDError $error)
	{
		$data = array(
			$error->service()
			,$error->internal()
			,$error->userId()
			,$error->crsId()
			,$error->rowId()
			,$error->reason()
			,$error->message()
			);

		var_dump($data);
		die("WBD Action failed");
	}

	protected function prepareErrorStatement()
	{
		$sql = 'INSERT INTO wbd_errors ('
			.'		action, internal, usr_id, crs_id,'
			.'		internal_booking_id, reason, reason_full'
			.'	) VALUES (?,?,?,?,?,?,?)';
		$data_types = array('text','integer','integer','integer','integer','text','text');
		$this->error_statement = $this->gDB->prepareManip($sql, $data_types);
	}

	/**********************************
	*
	* GET NEXT RECORD
	*
	**********************************/
	/**
	* get the next request object
	*
	* @return 	WBDRequest
	*/
	public function getNextRequest()
	{
		return array_shift($this->requests);
	}

	/**********************************
	*
	* USEFUL FUNCTIONS
	*
	**********************************/
	/**
	 * raises the event user has changed
	 *
	 * @param ilObjUser $user
	 */
	public function raiseEventUserChanged(ilObjUser $user)
	{
		$this->gAppEventHandler->raise("Services/User", "afterUpdate", array("user_obj" => $user));
	}

	/**
	* set last_wbd_report for automaticly created hist rows
	*
	* @param integer 	$a_user_id
	*/
	public function setLastWBDReportForAutoHistRows($a_user_id)
	{
		$sql = "SELECT row_id FROM hist_user\n"
				." WHERE user_id = ".$this->gDB->quote($a_user_id, 'integer')."\n"
				." AND hist_historic = 0\n";
		$result = $this->gDB->query($sql);
		$record = $this->gDB->fetchAssoc($result);
		$this->setLastWBDReport('hist_user', array($record['row_id']));
	}

	/**
	* set WBD Booking id on usercoursestatus row
	*
	* @param int 		$row_id
	* @param string 	$booking_id
	*/
	public function setBookingId($row_id, $booking_id)
	{
		$sql = "UPDATE hist_usercoursestatus\n"
				." SET wbd_booking_id = ".$this->gDB->quote($booking_id, "text")."\n"
				." WHERE row_id = ".$this->gDB->quote($row_id, "integer")."\n";
		$result = $this->gDB->query($sql);
	}

	public function requestsCount()
	{
		return count($this->requests);
	}

	/**
	* set next wbd action to nothing
	*
	* @param string $user_id
	*/
	public function setNextWBDActionToNothing($user_id)
	{
		$wbd = $this->getWBDInstance($user_id);
		$wbd->setNextWBDAction(gevWBD::USR_WBD_NEXT_ACTION_NOTHING);
	}

	/**
	* sets the rows for storno
	*
	* @param array 		$storno_rows
	*/
	public function setStornoRows($storno_rows)
	{
		$this->storno_rows = $storno_rows;
	}

	/**
	* sets the users id's to recieve wp
	*
	* @param array 		$abfrage_usr_ids
	*/
	public function setAbfrageUsrIds($abfrage_usr_ids)
	{
		$this->abfrage_usr_ids = $abfrage_usr_ids;
	}

	protected function performPreliminaryChecks(array $checks_to_release, gevWBD $wbd)
	{
		return array_filter(
			$checks_to_release,
			function ($v) use ($wbd) {
				if (!$v->performCheck($wbd)) {
					return $v;
				}
			}
		);
	}

	protected function getWBDInstance($user_id)
	{
		return gevWBD::getInstanceByObjOrId($user_id);
	}
}
