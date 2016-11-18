<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Databeseabstraction for cron job
 */
class ilEffectivenessAnalysisReminderDB {
	const TABLE_NAME = "eff_analysis_maillog";

	/**
	 * @var ilDB
	 */
	protected $db;

	public function __construct(ilDB $db) {
		$this->db = $db;
	}

	/**
	 * install the plugin
	 */
	public function install() {
		$this->createTable();
	}

	/**
	 * Create table for maillog
	 */
	protected function createTable() {
		if(!$this->db->tableExists(self::TABLE_NAME)) {
			$fields = array(
				'crs_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				),
				'superior_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				),
				'type' => array(
					'type' => 'text',
					'length' => 10,
					'notnull' => true
				),
				'send' => array(
					'type' =>'date',
					'notnull' => true)
			);

			$this->db->createTable(self::TABLE_NAME, $fields);
		}
	}

	/**
	 * Save state if reminder is send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 * @param string 		$type
	 */
	public function reminderSend($crs_id, $superior_id, $type) {
		$values = array("crs_id" => array("integer", $crs_id)
					  , "superior_id" => array("integer", $superior_id)
					  , "type" => array("text", $type)
					  , "send" => array("text", date("Y-m-d"))
			);

		$this->db->insert(self::TABLE_NAME, $values);
	}

	/**
	 * Should the first reminder be send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 * @param string 	$type_first
	 *
	 * @return bool
	 */
	public function getNumRowsOfSentFirstReminder($crs_id, $superior_id, $type_first) {
		$query = "SELECT send\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE crs_id = ".$this->db->quote($crs_id, "integer")."\n"
				."     AND type = ".$this->db->quote($type_first, "text")."\n"
				."     AND superior_id = ".$this->db->quote($superior_id, "integer");

		$result = $this->db->query($query);
		return $this->db->numRows($result);

		
	}

	/**
	 * Should the second reminder be send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 * @param string 	$type_first
	 * @param string 	$type_second
	 *
	 * @return bool
	 */
	public function getLastSendDates($crs_id, $superior_id, $type_first, $type_second) {
		$query = "SELECT MAX(first.send) AS first_send, MAX(second.send) AS second_send\n"
				." FROM ".self::TABLE_NAME." first\n"
				." LEFT JOIN ".self::TABLE_NAME." second\n"
				."     ON second.crs_id = ".$this->db->quote($crs_id, "integer")."\n"
				."         AND second.type = ".$this->db->quote($type_second, "text")."\n"
				."         AND second.superior_id = ".$this->db->quote($superior_id, "integer")."\n"
				." WHERE first.crs_id = ".$this->db->quote($crs_id, "integer")."\n"
				."     AND first.type = ".$this->db->quote($type_first, "text")."\n"
				."     AND first.superior_id = ".$this->db->quote($superior_id, "integer");

		$result = $this->db->query($query);
		$row = $this->db->fetchAssoc($result);

		return $row;
	}
}