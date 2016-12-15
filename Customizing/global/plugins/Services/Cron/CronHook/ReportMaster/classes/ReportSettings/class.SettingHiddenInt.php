<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingHidden.php';

class SettingHiddenInt extends SettingHidden {
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return 0;
	}
}