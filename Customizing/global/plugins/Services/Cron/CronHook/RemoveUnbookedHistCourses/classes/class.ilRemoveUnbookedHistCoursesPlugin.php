<?php
include_once("./Services/Repository/classes/class.ilCronHookPlugin.php");
require_once(__DIR__."/../vendor/autoload.php");

use CaT\Plugins\RemoveUnbookedHistCourses;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilRemoveUnbookedHistCoursesPlugin extends ilCronHookPlugin
{
	/**
	 * @var RemoveUnbookedHistCourses\ilActions
	 */
	protected $actions;

	/**
	 * @var RemoveUnbookedHistCourses\ilDB
	 */
	protected $data_base;

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
	public function getCronJobInstance($a_job_id)
	{
		require_once $this->getDirectory()."/classes/class.ilRemoveUnbookedHistCoursesJob.php";
		return new ilRemoveUnbookedHistCoursesJob();
	}

	/**
	 * Get actions
	 *
	 * @return ilActions
	 */
	public function getActions()
	{
		if ($this->actions === null) {
			global $ilDB;
			$this->actions = new RemoveUnbookedHistCourses\ilActions($this, $this->getDatabase($ilDB));
		}

		return $this->actions;
	}

	/**
	 * Get database actions
	 *
	 * @param \ilDB 	$db
	 *
	 * @return RemoveUnbookedHistCourses\ilDB
	 */
	protected function getDatabase(\ilDB $db)
	{
		if ($this->data_base === null) {
			$this->data_base = new RemoveUnbookedHistCourses\ilDB($db);
		}

		return $this->data_base;
	}
}
