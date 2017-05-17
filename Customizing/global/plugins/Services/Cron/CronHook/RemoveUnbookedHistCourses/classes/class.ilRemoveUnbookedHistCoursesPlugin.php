<?php
include_once("./Services/Repository/classes/class.ilCronHookPlugin.php");

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
	 * @return il<PLUGINNAME>Job[]
	 */
	public function getCronJobInstances()
	{
	}

	/**
	 * Get a single cronjob object
	 *
	 * @return il<PLUGINNAME>Job
	 */
	public function getCronJobInstance()
	{
	}
}
