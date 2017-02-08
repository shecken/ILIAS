<?php

class ilDB extends DB
{
	const VA_PASS_TABLE = "va_pass";

	/**
	 * @var ilDB
	 */
	protected $db;

	public function __construct(ilDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function install()
	{
		$this->createTable();
	}

	/**
	 * @inheritdoc
	 */
	public function read($obj_id)
	{
		assert('is_int($obj_id)');
		$query = "SELECT obj_id, sp_node_ref_id, online\n"
				." FROM ".self::VA_PASS_TABLE."\n"
				." WHERE obj_id = ".$this->db->quote($obj_id, "integer");

		$res = $this->db->query($query);

		if ($this->db->numRows($res) == 0) {
			throw new Exception("No settings data for obj_id: $obj_id found");
		}

		$row = $this->db->fetchAssoc($res);

		return new VAPass($obj_id, $row["online"], $row["sp_node_ref_id"]);
	}

	/**
	 * @inheritdoc
	 */
	public function insert(VAPass $va_pass)
	{
		$values = array("obj_id" => array("integer", $va_pass->getObjId())
					   ," sp_node_ref_id" => array("integer", $va_pass->getSPNodeRefId())
					   ," online" => array("integer", $va_pass->getOnline())
			);

		$this->db->insert(self::VA_PASS_TABLE, $values);
	}

	/**
	 * @inheritdoc
	 */
	public function update(VAPass $va_pass)
	{
		$where = array("obj_id" => array("integer", $va_pass->getObjId()));

		$values = array(" sp_node_ref_id" => array("integer", $va_pass->getSPNodeRefId())
					   ," online" => array("integer", $va_pass->getOnline())
			);

		$this->db->update(self::VA_PASS_TABLE, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id)
	{
		$query = "DELETE FROM ".self::VA_PASS_TABLE."\n"
				." WHERE obj_id = ".$this->db->quote($obj_id, "integer");

		$this->db->manipulate($query);
	}

	/**
	 * Table is created
	 */
	protected function createTable()
	{
		if (!$this->db->tableExists(self::VA_PASS_TABLE)) {
			$fields =
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'sp_node_ref_id' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> true
					),
					'online' => array(
						'type' 		=> 'integer',
						'length'	=> 1,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::VA_PASS_TABLE, $fields);
		}
	}
}
