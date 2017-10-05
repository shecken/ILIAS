<?php

/**
 * cat-tms-patch start
 */

interface TrainingSearchDB {
	/**
	 * Get courses user can book
	 *
	 * @param int 	$user_id
	 *
	 * @return array<int, ilObjCourse | ilObjBookingModalities[] | ilObjCourseClassification>
	 */
	public function getBookableTrainingsFor($user_id);

	/**
	 * Create new bookable course
	 *
	 * @param string 	$crs_title
	 * @param string 	$type
	 * @param string 	$start_date_str
	 * @param int 	$bookings_available
	 * @param string[] 	$target_group
	 * @param string 	$goals
	 * @param string[] 	$topics
	 * @param string 	$end_date_str
	 * @param string 	$city
	 * @param string 	$address
	 * @param string 	$costs
	 *
	 * @return BookableCourse
	 */
	public function getBookableCourse($crs_title,
				$type,
				$start_date_str,
				$bookings_available,
				array $target_group,
				$goals,
				array $topics,
				$end_date_str,
				$city,
				$address,
				$costs = "KOSTEN"
	);
}