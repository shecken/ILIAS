<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.Setting.php';

class SettingRichText extends Setting {
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return "";
	}
}