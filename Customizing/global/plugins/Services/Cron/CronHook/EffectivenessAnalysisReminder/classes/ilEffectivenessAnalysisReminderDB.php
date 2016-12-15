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

	public function updateTable() {
		$field = array(
					'type' => 'text',
					'length' => 255,
					'notnull' => true
				);

		if(!$this->db->tableColumnExists(self::TABLE_NAME, "user_ids")) {
			$this->db->addTableColumn(self::TABLE_NAME, "user_ids", $field);
		}
	}

	/**
	 * Create table for maillog
	 */
	protected function createTable() {
		if(!$this->db->tableExists(self::TABLE_NAME)) {
			$fields = array(
				'id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				),
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
			$this->db->addPrimaryKey(self::TABLE_NAME, array('id'));
			$this->db->createSequence(self::TABLE_NAME);
		}
	}

	/**
	 * Save state if reminder is send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 * @param string 	$type
	 * @param int[]		$user_ids
	 */
	public function logMailSend($crs_id, $superior_id, $type, $user_ids) {
		$user_ids = base64_encode(serialize($user_ids));
		$next_id = $this->db->nextId(self::TABLE_NAME);

		$values = array("id" => array("integer", $next_id)
					  , "crs_id" => array("integer", $crs_id)
					  , "superior_id" => array("integer", $superior_id)
					  , "type" => array("text", $type)
					  , "send" => array("text", date("Y-m-d"))
					  , "user_ids" => array("text", $user_ids)
			);

		$this->db->insert(self::TABLE_NAME, $values);
	}

	/**
	 * Get user_ids where $type mail is send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 * @param string 	$type
	 *
	 * @return array<int[]>
	 */
	public function getUserIdsTypeIsSend($crs_id, $superior_id, $type) {
		$query = "SELECT send, user_ids\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE crs_id = ".$this->db->quote($crs_id, "integer")."\n"
				."     AND type = ".$this->db->quote($type, "text")."\n"
				."     AND superior_id = ".$this->db->quote($superior_id, "integer");

		$result = $this->db->query($query);
		$ret = array();
		while($row = $this->db->fetchAssoc($result)) {
			$ret[$row["send"]] = unserialize(base64_decode($row["user_ids"]));
		}

		return $ret;
	}
}