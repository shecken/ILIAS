<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Cron/classes/class.ilCronJob.php";

/**
* Implementation of the cron job
*/
class ilRemoveUnbookedHistCoursesJob extends ilCronJob
{

	/**
	 * Get id
	 *
	 * @return string
	 */
	public function getId()
	{
	}

	/**
	 * Is to be activated on "installation"
	 *
	 * @return boolean
	 */
	public function hasAutoActivation()
	{
	}

	/**
	 * Can the schedule be configured?
	 *
	 * @return boolean
	 */
	public function hasFlexibleSchedule()
	{
	}

	/**
	 * Get schedule type
	 *
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
	}

	/**
	 * Get schedule value
	 *
	 * @return int|array
	 */
	public function getDefaultScheduleValue()
	{
	}

	/**
	 * Get called if the cronjob is started
	 * Executing the ToDo's of the cronjob
	 */
	public function run()
	{
	}
}
