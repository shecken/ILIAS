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
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				);

		if(!$this->db->tableColumnExists(self::TABLE_NAME, "user_id")) {
			$this->db->addTableColumn(self::TABLE_NAME, "user_id", $field);
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
		$values = array("crs_id" => array("integer", $crs_id)
						  , "superior_id" => array("integer", $superior_id)
						  , "type" => array("text", $type)
						  , "send" => array("text", date("Y-m-d"))
				);

		foreach($user_ids as $key => $user_id) {
			$next_id = $this->db->nextId(self::TABLE_NAME);
			$values["id"] = array("integer", $next_id);
			$values["user_id"] = array("text", $user_id);
			$this->db->insert(self::TABLE_NAME, $values);
		}
	}
}