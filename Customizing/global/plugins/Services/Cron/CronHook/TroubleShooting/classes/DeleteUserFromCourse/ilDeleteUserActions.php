<?php

require_once __DIR__."/DeleteUserDB.php";

class ilDeleteUserActions {

	public function __construct(DeleteUserDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Get last hostory information for user and course
	 *
	 * @param int 	$crs_id
	 * @param int 	$user_id
	 *
	 * @return string[]
	 */
	public function getHistRowForCourseAndUserId($crs_id, $user_id)
	{
		assert('is_int($crs_id)');
		assert('is_int($user_id)');
		return $this->db->getHistRowForCourseAndUserId($crs_id, $user_id);
	}

	/**
	 * Checks a course is finalized or not
	 *
	 * @param int 	$crs_id
	 *
	 * @return bool
	 */
	public function isCourseFinalized($crs_id)
	{
		assert('is_int($crs_id)');
		return $this->db->isCourseFinalized($crs_id);
	}

	/**
	 * Set a course back to unfinalized state
	 *
	 * @param int 	$crs_id
	 *
	 * @return void
	 */
	public function setCourseUnfinalzied($crs_id)
	{
		assert('is_int($crs_id)');
		return $this->db->setCourseUnfinalzied($crs_id);
	}

	/**
	 * Set a course to finalized state
	 *
	 * @param int 	$crs_id
	 *
	 * @return void
	 */
	public function setCourseFinalized($crs_id)
	{
		assert('is_int($crs_id)');
		return $this->db->setCourseFinalized($crs_id);
	}
}