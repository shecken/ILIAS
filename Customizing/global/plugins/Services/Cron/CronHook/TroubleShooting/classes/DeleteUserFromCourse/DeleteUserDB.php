<?php

interface DeleteUserDB {
	/**
	 * Get last history information for user and course
	 *
	 * @param int 	$crs_id
	 * @param int 	$user_id
	 *
	 * @return string[]
	 */
	public function getWBDBookingInfos($crs_id, $user_id);

	/**
	 * Checks a course is finalized or not
	 *
	 * @param int 	$crs_id
	 *
	 * @return bool
	 */
	public function isCourseFinalized($crs_id);

	/**
	 * Set a course back to unfinalized state
	 *
	 * @param int 	$crs_id
	 *
	 * @return void
	 */
	public function setCourseUnfinalzied($crs_id);

	/**
	 * Set a course to finalized state
	 *
	 * @param int 	$crs_id
	 *
	 * @return void
	 */
	public function setCourseFinalized($crs_id);
}