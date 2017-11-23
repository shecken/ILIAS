<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

use ILIAS\TMS\Mailing\LogEntry;
use ILIAS\TMS\Mailing\LoggingDB;

/**
 * Implemention for DB
 */
class ilTMSMailingLogsDB implements LoggingDB {

	const TABLE_NAME = "mail_logs";

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db){
		$this->db = $db;
	}

	/**
	 * Get next id for entry
	 */
	public function getNextId() {
		return (int)$this->db->nextId(static::TABLE_NAME);
	}

	/**
	 *@inheritdoc
	 */
	public function log($context, $usr_id, $mail_id, $crs_ref_id = null, $subject = '', $msg = '') {
		$id = $this->getNextId();
		$date = new \ilDateTime(date('c'), IL_CAL_DATETIME);
		$usr_login = \ilObjUser::_lookupLogin($usr_id);
		$usr_name = \ilObjUser::_lookupFullname($usr_id);
		$usr_mail = \ilObjUser::_lookupEmail($usr_id);

		$entry = new LogEntry($id, $date, $context,
			$usr_id, $usr_login, $usr_name, $usr_mail,
			$mail_id, $crs_ref_id, $subject, $msg);

		$values = array(
			"id" => array("integer", $entry->getId()),
			"datetime" => array("text", $entry->getDate()->get(IL_CAL_DATETIME)),
			"context" => array("text", $entry->getContext()),
			"mail_id" => array("text", $entry->getMailId()),
			"usr_id" => array("integer", $entry->getUserId()),
			"usr_login" => array("text", $entry->getUserLogin()),
			"usr_name" => array("text", $entry->getUserName()),
			"usr_mail" => array("text", $entry->getUserMail()),
			"subject" => array("text", $entry->getSubject()),
			"msg" => array("text", $entry->getMessage()),
		);
		if(! is_null($entry->getCourseRefId())) {
			$values["crs_ref_id"] = array("integer", $entry->getCourseRefId());
		}

		$this->db->insert(static::TABLE_NAME, $values);
		return $entry;
	}

	/**
	 *@inheritdoc
	 */
	public function	selectForCourse($ref_id, $sort=null, $limit=null) {
		assert('is_int($ref_id)');
		$query = "SELECT" .PHP_EOL
				." id, datetime, context, crs_ref_id,"
				." usr_id, usr_login, usr_name, usr_mail, "
				." mail_id, subject, msg" .PHP_EOL
				." FROM ".static::TABLE_NAME .PHP_EOL
				." WHERE crs_ref_id = " .$this->db->quote($ref_id, "integer")
				.PHP_EOL;

		if($sort) {
			list($field, $direction) = $sort;
			$query .= ' ORDER BY ' .$field  .' ' .strtoupper($direction) .PHP_EOL;
		}
		if($limit) {
			list($length, $offset) = $limit;
			$query .= ' LIMIT ' .$this->db->quote($length, "integer");
			$query .= ' OFFSET ' .$this->db->quote($offset, "integer");
		}

		$ret = array();
		$result = $this->db->query($query);
		while ($row = $this->db->fetchAssoc($result)) {
			$entry = new LogEntry(
				(int)$row["id"],
				new \ilDateTime($row["datetime"], IL_CAL_DATETIME),
				(string)$row["context"],
				(int)$row["usr_id"],
				(string)$row["usr_login"],
				(string)$row["usr_name"],
				(string)$row["usr_mail"],
				(string)$row["mail_id"],
				(int)$row["crs_ref_id"],
				(string)$row["subject"],
				(string)$row["msg"]
			);
			$ret[] = $entry;
		}
		return $ret;
	}

	/**
	 *@inheritdoc
	 */
	public function	selectCountForCourse($ref_id) {
		$query = "SELECT COUNT ('id') FROM " .static::TABLE_NAME.PHP_EOL
			." WHERE crs_ref_id = " .$this->db->quote($ref_id, "integer");
		$result = $this->db->query($query);
		$ret = (int)array_values($this->db->fetchAssoc($result))[0];
		return $ret;
	}


	/**
	 * create table
	 *
	 * @return void
	 */
	public function createTable() {
		if (!$this->db->tableExists(static::TABLE_NAME)) {
			$fields = array(
				'id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> true
				),
				'datetime' => array(
					'type' => 'timestamp',
					'notnull' => true
				),
				'context' => array(
					'type' 		=> 'text',
					'length' 	=> 64,
					'notnull' 	=> true
				),
				'crs_ref_id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> false
				),
				'mail_id' => array(
					'type' 		=> 'text',
					'length' 	=> 64,
					'notnull' 	=> true
				),
				'usr_id' => array(
					'type' 		=> 'integer',
					'length' 	=> 4,
					'notnull' 	=> true
				),
				'usr_login' => array(
					'type' 		=> 'text',
					'length' 	=> 255,
					'notnull' 	=> true
				),
				'usr_mail' => array(
					'type' 		=> 'text',
					'length' 	=> 255,
					'notnull' 	=> true
				),
				'usr_name' => array(
					'type' 		=> 'text',
					'length' 	=> 255,
					'notnull' 	=> false
				),
				'subject' => array(
					'type' 		=> 'clob',
					'notnull' 	=> false
				),
				'msg' => array(
					'type' 		=> 'clob',
					'notnull' 	=> false
				)
			);
			$this->db->createTable(static::TABLE_NAME, $fields);
		}
	}

	/**
	 * Configure primary key on table
	 *
	 * @return void
	 */
	public function createPrimaryKey(){
		$this->db->addPrimaryKey(static::TABLE_NAME, array("id"));
	}

	/**
	 * Create sequences for ids
	 *
	 * @return void
	 */
	public function createSequence(){
		$this->db->createSequence(static::TABLE_NAME);
	}

}
