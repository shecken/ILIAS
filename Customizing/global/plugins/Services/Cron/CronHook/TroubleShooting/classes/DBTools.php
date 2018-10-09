<?php

/**
 * Pool of functions we need for more than one DB abstraction
 */
trait DBTools {
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
		$query = "SELECT state FROM crs_pstatus_crs WHERE crs_id = ".$crs_id;
		$res = $this->db->query($query);
		$row = $this->db->fetchAssoc($res);

		return $row["state"] == self::FINALIZED_STATE;
	}

	/**
	 * @inheritdoc
	 */
	public function setCourseFinalized($crs_id)
	{
		assert('is_int($crs_id)');
		$query = "UPDATE crs_pstatus_crs SET state = ".self::FINALIZED_STATE." WHERE crs_id = ".$crs_id;
		$this->db->manipulate($query);
	}
}