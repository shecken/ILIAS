<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once(__DIR__."/../vendor/autoload.php");

use \CaT\Plugins\ReportStudyProgramme\Settings;

class ilReportStudyProgrammePlugin extends ilRepositoryObjectPlugin
{
	/**
	 * @var CaT\Plugins\ReportStudyProgramme\Settings\DB
	 */
	protected $settings_db;

	// must correspond to the plugin subdirectory
	public function getPluginName()
	{
		return "ReportStudyProgramme";
	}

	/**
	 * Get name of gui class handling the commands
	 */
	public function getGuiClass()
	{
		return "ilObjReportStudyProgrammeGUI";
	}

	/**
	 * create (if not available) and returns SettingsDB
	 *
	 * @return \CaT\Plugins\ReportStudyProgramme\Settings\DB
	 */
	public function getReportStudyProgrammeDB()
	{
		global $ilDB;
		if ($this->settings_db === null) {
			$this->settings_db = new Settings\ilDB($ilDB);
		}
		return $this->settings_db;
	}

	/**
	 * Get a closure to get txts from plugin.
	 *
	 * @return \Closure
	 */
	public function txtClosure()
	{
		return function ($code) {
			return $this->txt($code);
		};
	}
}
