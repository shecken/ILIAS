<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");


class gevADPImportJob extends ilCronJob
{
	public function getId()
	{
		return "gev_adp_import";
	}
	
	public function getTitle()
	{
		return "Import von ADP Nummern";
	}

	public function hasAutoActivation()
	{
		return true;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}
	
	public function getDefaultScheduleType()
	{
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return 1;
	}
}