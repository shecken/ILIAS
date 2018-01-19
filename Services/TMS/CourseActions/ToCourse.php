<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

use ILIAS\TMS;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ToCourse extends TMS\CourseActionImpl
{
	/**
	 * @inheritdoc
	 */
	public function isAllowedFor($usr_id)
	{
		$course = $this->entity->object();
		return $this->hasAccess($course->getRefId());
	}

	/**
	 * @inheritdoc
	 */
	public function getLink(\ilCtrl $ctrl, $usr_id)
	{
		$course = $this->entity->object();

		require_once("Services/Link/classes/class.ilLink.php");
		return ilLink::_getStaticLink($course->getRefId(), "crs");
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel()
	{
		global $DIC;
		$lng = $DIC->language();
		return $lng->txt("to_course");
	}

	/**
	 * Has user read access to the course
	 *
	 * @param int 	$crs_ref_id
	 *
	 * @return bool
	 */
	protected function hasAccess($crs_ref_id)
	{
		global $DIC;
		$access = $DIC->access();
		$course = $this->entity->object();
		if($access->checkAccess("read", "", $crs_ref_id)) {
			return true;
		}

		return false;
	}

	/**
	 * Is today in booking period of course
	 *
	 * @param ilDateTime 	$crs_start
	 * @param int 	$booking_start
	 * @param int 	$booking_end
	 *
	 * @return bool
	 */
	public function isInBookingPeriod(\ilDateTime $crs_start = null, $booking_start, $booking_end)
	{
		if ($crs_start == null) {
			return true;
		}

		$today_string = date("Y-m-d");

		$booking_start_date = clone $crs_start;
		$booking_start_date->increment(\ilDateTime::DAY, -1 * $booking_start);
		$start_string = $booking_start_date->get(IL_CAL_DATE);

		$booking_end_date = clone $crs_start;
		$booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_end);
		$end_string = $booking_end_date->get(IL_CAL_DATE);

		if ($today_string >= $start_string && $today_string <= $end_string) {
			return true;
		}

		return false;
	}
}
