<?php
include_once("./Services/Repository/classes/class.ilCronHookPlugin.php");
require_once(__DIR__."/../vendor/autoload.php");

use CaT\Plugins\RemoveUnbookedHistCourses\ilActions;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilRemoveUnbookedHistCoursesPlugin extends ilCronHookPlugin
{
	/**
	 * Get the name of the Plugin
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return "RemoveUnbookedHistCourses";
	}

	/**
	 * Get an array with 1 to n numbers of cronjob objects
	 *
	 * @return ilRemoveUnbookedHistCoursesJob[]
	 */
	public function getCronJobInstances()
	{
		require_once $this->getDirectory()."/classes/class.ilRemoveUnbookedHistCoursesJob.php";
		$job = new ilRemoveUnbookedHistCoursesJob();
		return array($job);
	}

	/**
	 * Get a single cronjob object
	 *
	 * @return ilRemoveUnbookedHistCoursesJob
	 */
	public function getCronJobInstance()
	{
		require_once $this->getDirectory()."/classes/class.ilRemoveUnbookedHistCoursesJob.php";
		return new ilRemoveUnbookedHistCoursesJob();
	}
}
