<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");


class gevCrsReminderJob extends ilCronJob {
	public function getId() {
		return "gev_crs_reminder";
	}
	
	public function getTitle() {
		return "Sends reminder to admin when course has too little participants.";
	}

	public function hasAutoActivation() {
		return true;
	}
	
	public function hasFlexibleSchedule() {
		return false;
	}
	
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue() {
		return 1;
	}
	
	public function run() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		
		global $ilLog, $ilDB;
		
		$start_date_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$end_date_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
		
		$cron_result = new ilCronJobResult();
		
		$now = date("Y-m-d");
		
		$query = "SELECT DISTINCT cs.obj_id ".
				 "  FROM crs_settings cs ".
				 " LEFT JOIN object_reference oref".
				 "   ON cs.obj_id = oref.obj_id".
				 "  JOIN adv_md_values_date start_date ".
				 "    ON cs.obj_id = start_date.obj_id ".
				 "   AND start_date.field_id = ".$ilDB->quote($start_date_field_id, "integer").
				 " WHERE ADDDATE(start_date.value, -30)".
				 "       <= ".$ilDB->quote($now, "date").
				 "   AND start_date.value >= ".$ilDB->quote($now, "date").
				 "   AND oref.deleted IS NULL".
				 "";

		$res = $ilDB->query($query);
		
		while ($rec = $ilDB->fetchAssoc($res)) {
			$crs_id = $rec["obj_id"];
			$ilLog->write("gevCrsReminderJob::run: Checking amount of participants of course ".$crs_id.".");
			
			$utils = gevCourseUtils::getInstance($crs_id);
			if ($utils->getMinParticipants() > count($utils->getParticipants())) {
				$auto_mails = new gevCrsAutoMails($crs_id);
				$auto_mails->send("min_participants_not_reached");
			}
			
			
			// i'm alive!
			ilCronManager::ping($this->getId());
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}

?>