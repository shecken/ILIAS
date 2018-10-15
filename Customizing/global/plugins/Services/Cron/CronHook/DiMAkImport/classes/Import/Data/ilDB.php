<?php

namespace CaT\Plugins\DiMAkImport\Import\Data;

class ilDB implements DB {
	const TABLE_NAME = "dimak_agent_number";

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function truncate()
	{
		$query = "DELETE FROM ".self::TABLE_NAME;
		$this->db->manipulate($query);
	}

	public function save($agent_number)
	{
		assert('is_int($agent_number)');
		$values = array(
			"agent_number" => array("integer", $agent_number)
		);

		$this->db->insert(self::TABLE_NAME, $values);
	}

	public function checkAgendNumber($agent_number)
	{
		assert('is_int($agent_number)');
		$query = "SELECT count(agent_number) AS cnt".PHP_EOL
			." FROM ".self::TABLE_NAME.PHP_EOL
			." WHERE agent_number = ".$this->db->qoute($agent_number, "integer");

		$res = $this->db->query($query);
		$row = $this->db->fetchAssoc($res);

		return $row["cnt"] > 0;
	}

	public function createTable()
	{
		if(!$this->db->tableExists(self::TABLE_NAME)) {
			$fields = 
				array('agent_number' => array(
						'type' 		=> 'integer',
						'length' 	=> 8,
						'notnull' 	=> true
					)
				);

			$this->db->createTable(self::TABLE_NAME, $fields);
			
		}
	}

	public function createPrimaryKey()
	{
		$this->db->addPrimaryKey(self::TABLE_NAME, array("agent_number"));
	}
}