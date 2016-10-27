<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingInt.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingString.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingFloat.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingBool.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingListInt.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingHiddenInt.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingHiddenString.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingRichText.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingText.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.ReportSettings.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.ReportSettingsDataHandler.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.ReportSettingsFormHandler.php';
class SettingFactory {
	protected $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	public function settingInt($id, $name) {
		return new SettingInt($id, $name);
	}

	public function settingString($id, $name) {
		return new SettingString($id, $name);
	}

	public function settingFloat($id, $name) {
		return new SettingFloat($id, $name);
	}

	public function settingBool($id, $name) {
		return new SettingBool($id, $name);
	}

	public function settingRichText($id, $name) {
		return new SettingRichText($id, $name);
	}

	public function settingText($id, $name) {
		return new SettingText($id, $name);
	}

	public function settingListInt($id, $name) {
		return new SettingListInt($id, $name);
	}

	public function settingHiddenInt($id, $name) {
		return new SettingHiddenInt($id, $name);
	}

	public function settingHiddenString($id, $name) {
		return new SettingHiddenString($id, $name);
	}

	public function reportSettings($table) {
		return new ReportSettings($table, $this->db);
	}

	public function reportSettingsDataHandler() {
		return new ReportSettingsDataHandler($this->db);
	}

	public function reportSettingsFormHandler() {
		return new ReportSettingsFormHandler();
	}
}