<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * Interface for DB handle of Mail-Logs
 */
interface LoggingDB {
	/**
	 * Create a new log entry
	 *
	 * @param int  	$crs_ref_id
	 * @param int  	$usr_id
	 * @param string  	$mail_id
	 * @param string  	$msg
	 *
	 * @return LogEntry
	 */
	public function log($crs_ref_id, $usr_id, $mail_id, $msg = '');

	/**
	 * Get logs for course's ref_id
	 *
	 * @param int $ref_id
	 * @param string[]|null $sort 	array(field, "asc"|"desc")|null
	 * @param int[]|null $limit 	array(length, offset)|null
	 *
	 * @return LogEntry[]
	 */
	public function	selectForCourse($ref_id, $sort=null, $limit=null);

	/**
	 * Get number of entries for course.
	 *
	 * @param int $ref_id
	 *
	 * @return int
	 */
	public function	selectCountForCourse($ref_id);


}
