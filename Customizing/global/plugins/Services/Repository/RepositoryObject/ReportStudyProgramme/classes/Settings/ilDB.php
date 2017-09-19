<?php

namespace CaT\Plugins\ReportStudyProgramme\Settings;

/**
 * Implementation of Db abstraction for ILIAS
 */
class ilDB implements DB
{
	const XSP_TABLE = "xsp";

	/**
	 * @var ilDB
	 */
	protected $db;

	public function __construct(\ilDB $db)
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
		$query = "SELECT obj_id, sp_node_ref_id, is_online\n"
				." FROM ".self::XSP_TABLE."\n"
				." WHERE obj_id = ".$this->db->quote($obj_id, "integer");

		$res = $this->db->query($query);

		if ($this->db->numRows($res) == 0) {
			throw new \Exception("No settings data for obj_id: $obj_id found");
		}

		$row = $this->db->fetchAssoc($res);

		return new ReportStudyProgramme($obj_id, $row["sp_node_ref_id"], (bool)$row["is_online"]);
	}

	/**
	 * @inheritdoc
	 */
	public function insert(ReportStudyProgramme $xsp_pass)
	{
		$values = array("obj_id" => array("integer", $xsp_pass->getObjId())
					   ," sp_node_ref_id" => array("integer", $xsp_pass->getSPNodeRefId())
					   ," is_online" => array("integer", $xsp_pass->getOnline())
			);

		$this->db->insert(self::XSP_TABLE, $values);
	}

	/**
	 * @inheritdoc
	 */
	public function update(ReportStudyProgramme $xsp_pass)
	{
		$where = array("obj_id" => array("integer", $xsp_pass->getObjId()));

		$values = array(" sp_node_ref_id" => array("integer", $xsp_pass->getSPNodeRefId())
					   ," is_online" => array("integer", $xsp_pass->getOnline())
			);

		$this->db->update(self::XSP_TABLE, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id)
	{
		$query = "DELETE FROM ".self::XSP_TABLE."\n"
				." WHERE obj_id = ".$this->db->quote($obj_id, "integer");

		$this->db->manipulate($query);
	}

	/**
	 * Table is created
	 */
	protected function createTable()
	{
		if (!$this->db->tableExists(self::XSP_TABLE)) {
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
					'is_online' => array(
						'type' 		=> 'integer',
						'length'	=> 1,
						'notnull' 	=> true
					)
				);

			$this->db->createTable(self::XSP_TABLE, $fields);
		}
	}

	public function update1()
	{
		if ($this->db->tableColumnExists(self::XSP_TABLE, "sp_node_ref_id")) {
			$this->db->modifyTableColumn(self::XSP_TABLE, "sp_node_ref_id", array('type' => 'integer',
																				  'length' => 4,
																				  'notnull' => false));
		}
		if ($this->db->tableColumnExists(self::XSP_TABLE, "is_online")) {
			$this->db->modifyTableColumn(self::XSP_TABLE, "is_online", array('type' => 'integer',
																			 'length' => 1,
																			 "notnull" => "false"));
		}
	}
}
