<?php

use CaT\IliasUserOrguImport as DUOI;

include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilUserOrguImportJob.php';
require_once 'Services/Repository/classes/class.ilRepUtil.php';


/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilUserOrguImportPlugin extends ilCronHookPlugin
{
	/**
	 * Get the name of the Plugin
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return "UserOrguImport";
	}

	/**
	 * Get an array with 1 to n numbers of cronjob objects
	 *
	 * @return il<PLUGINNAME>Job[]
	 */
	public function getCronJobInstances()
	{
		return [ new ilUserOrguImportJob($this)];
	}

	/**
	 * Get a single cronjob object
	 *
	 * @return il<PLUGINNAME>Job
	 */
	public function getCronJobInstance($a_job_id)
	{
		return new ilUserOrguImportJob($this);
	}

	public function getFactory(DUOI\ErrorReporting\ErrorCollection $errors) {
		global $DIC;
		return $f = new DUOI\Factory(
			$DIC['ilDB'],
			$DIC['rbacadmin'],
			$DIC['rbacreview'],
			$DIC['ilSetting'],
			ilObjOrgUnitTree::_getInstance(),
			$DIC['tree'],
			new ilRepUtil(),
			$errors,
			$DIC['ilLog']
		);
	}
}
