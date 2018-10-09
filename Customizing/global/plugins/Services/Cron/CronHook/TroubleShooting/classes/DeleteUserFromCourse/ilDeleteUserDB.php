<?php

require_once __DIR__."/DeleteUserDB.php";
require_once __DIR__."/../DBTools.php";

class ilDeleteUserDB implements DeleteUserDB {
	use DBTools;

	const FINALIZED_STATE = 3;
	const TEMP_STATE = 1;

	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function getWBDBookingInfos($crs_id, $user_id)
	{
		assert('is_int($crs_id)');
		assert('is_int($user_id)');

		$query = "SELECT last_wbd_Report, wbd_booking_id".PHP_EOL
			." FROM hist_usercoursestatus".PHP_EOL
			." WHERE crs_id = ".$this->db->quote($crs_id, "integer").PHP_EOL
			."     AND usr_id = ".$this->db->quote($crs_id, "integer").PHP_EOL
			."     AND hist_historic = 0";

		$res = $this->db->query($query);
		return $this->db->fetchAssoc($res);
	}

	/**
	 * @inheritdoc
	 */
	public function setCourseUnfinalzied($crs_id)
	{
		assert('is_int($crs_id)');
		$query = "UPDATE crs_pstatus_crs SET state = ".self::TEMP_STATE." WHERE crs_id = ".$crs_id;
		$this->db->manipulate($query);
	}
}