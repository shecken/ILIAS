<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once(__DIR__."/../vendor/autoload.php");

use \CaT\Plugins\ReportVAPass\Settings;

class ilReportVAPassPlugin extends ilRepositoryObjectPlugin
{
	/**
	 * @var CaT\Plugins\ReportVAPass\Settings\DB
	 */
	protected $settings_db;

	// must correspond to the plugin subdirectory
	public function getPluginName()
	{
		return "ReportVAPass";
	}

	/**
	 * Get name of gui class handling the commands
	 */
	public function getGuiClass()
	{
		return "ilObjReportVAPassGUI";
	}

	/**
	 * create (if not available) and returns SettingsDB
	 *
	 * @return \CaT\Plugins\ReportVAPass\Settings\DB
	 */
	public function getVAPassDB()
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
