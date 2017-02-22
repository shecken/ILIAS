<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseListGUI.php';

class ilObjReportStudyProgrammeOverviewListGUI extends ilObjReportBaseListGUI
{

	/**
	* Init type
	*/
	public function initType()
	{
		$this->setType("xspo");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass()
	{
		return "ilObjReportStudyProgrammeOverviewGUI";
	}

	public function getProperties()
	{
		$props = array();
		$this->plugin->includeClass("class.ilObjReportStudyProgrammeOverviewAccess.php");

		if (!ilObjReportStudyProgrammeOverviewAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		return $props;
	}
}
