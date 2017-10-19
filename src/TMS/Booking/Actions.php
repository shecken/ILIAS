<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

/**
 * This encapsulates the basic steps that need to be done to get a valid
 * booking for a user on a course.
 */
interface Actions {
	/**
	 * Book the given user on the course. 
	 *
	 * @param	int		$crs_ref_id
	 * @param	int		$user_id
	 */
	public function bookUser($crs_ref_id, $user_id);
}

