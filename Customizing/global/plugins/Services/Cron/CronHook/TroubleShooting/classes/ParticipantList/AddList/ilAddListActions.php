<?php

require_once __DIR__."/AddListDB.php";

class ilAddListActions {

	public function __construct(AddListDB $db)
	{
		$this->db = $db;
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