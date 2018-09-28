<?php

require_once __DIR__."/ChangeListDB.php";

class ilChangeListActions {

	public function __construct(ChangeListDB $db)
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
}