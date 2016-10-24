<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseListGUI.php';
/**
 * @inheritdoc
 */
class ilObjReportExamBioListGUI extends ilObjReportBaseListGUI {
	/**
	 * Init type
	 */
	public function initType() {
		$this->setType("xexb");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportExamBioGUI";
	}

	public function getProperties() {
		$props = array();

		$this->plugin->includeClass("class.ilObjReportExamBioAccess.php");
		if (!ilObjReportExamBioAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		return $props;
	}
}