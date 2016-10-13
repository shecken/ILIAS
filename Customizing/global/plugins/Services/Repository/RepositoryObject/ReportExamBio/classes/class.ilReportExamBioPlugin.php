<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilReportBasePlugin.php';

class ilReportExamBioPlugin extends ilReportBasePlugin {

	protected function getReportName() {
		return "ReportExamBio";
	}
}