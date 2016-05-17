<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class		WBDCommunicationJob
 *
 * CronJob:	perform daily communication to WBD
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training-de>
 * @version $Id$
 */

require_once "Services/Cron/classes/class.ilCronManager.php";
require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";

class WBDCommunicationJob extends ilCronJob {

	private $gDB;
	private $gLog;
	private $gLng;
	private $gRbacadmin;

	public function __construct() {
		global $ilDB, $ilLog, $lng, $rbacadmin;
		$this->gDB = $ilDB;
		$this->gLog = $ilLog;
		$this->gLng = $lng;
		$this->gRbacadmin = $rbacadmin;
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getId() {
		return "wbd_communication";
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getTitle() {
		return $this->gLng->txt("wbd_communication_title");
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	bool
	 */
	public function hasAutoActivation() {
		return true;
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	bool
	 */
	public function hasFlexibleSchedule() {
		return false;
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	int
	 */
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	int
	 */
	public function getDefaultScheduleValue() {
		return 1;
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	ilCronJobResult
	 */
	public function run() {
		$cron_result = new ilCronJobResult();
		$this->gLog->write("### ReportMaster: STARTING ###");
		ilCronManager::ping($this->getId());
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}