<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.Setting.php';

class SettingFloat extends Setting {

	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return 0;
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultToForm() {
		return function($val) {return number_format($val,2,".","");};
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm() {
		return function($val) {
				return (float)$val;};
	}
}