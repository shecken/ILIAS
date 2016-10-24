<?php

require_once("Services/GEV/DecentralTrainings/classes/BlankBuildingBlocks/BlankBuildingBlock.php");

/**
 * Database handle for blank building block informations
 */
class ilBlankDB {
	const TABLE_NAME = "dct_blank_bb_infos";

	public function __construct() {
		global $ilDB;

		$this->gDB = $ilDB;
	}

	public function install() {
		$this->createTable();
	}

	protected function createTable() {
		if(!$this->getDB()->tableExists(self::TABLE_NAME)) {
			$fields = 
				array('bb_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'crs_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> false
					),
					'request_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'content' => array(
						'type' 		=> 'clob',
						'notnull' 	=> true
					),
					'target' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
					)
				);

			$this->getDB()->createTable(self::TABLE_NAME, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_NAME, array("bb_id"));
		}
	}

	public function save(BlankBuildingBlock $blank_block) {
		$values = array
				( "bb_id" => array("integer", $blank_block->getBbId())
				, "crs_id" => array("float", $blank_block->getCrsId())
				, "request_id" => array("float", $blank_block->getRequestId())
				, "content" => array("text", $blank_block->getContent())
				, "target" => array("text", $blank_block->getTarget())
				);

		$this->getDB()->insert(self::TABLE_NAME, $values);
	}

	/**
	 * Get blank block informations for crs id and course building block
	 *
	 * @param int 	$bb_id 			id of course building block
	 * @param int 	$crs_id 		ref id of the crs
	 *
	 * @return BlankoBuildingBlock | null
	 */
	public function getBlankBuldingBlockForCourseBB($bb_id, $crs_id) {
		$query = $this->getSelectStatement();
		$query .= " WHERE bb_id = ".$this->getDB()->quote($bb_id, "integer")."\n"
				 ."     AND crs_id = ".$this->getDB()->quote($crs_id, "integer")."\n";

		$res = $this->getDB()->query($query);

		$ret = null;

		while($row = $this->getDB()->fetchAssoc($res)) {
			$ret = new BlankBuildingBlock($row["bb_id"]
					, $row["crs_id"]
					, $row["request_id"]
					, $row["content"]
					, $row["target"]
				);
		}

		return $ret;
	}

	/**
	 * Delete blank block information if course block is deleted
	 *
	 * @param int 	$crs_bb 	id of the course block
	 */
	public function deleteByCrsBB($crs_bb) {
		$query = "DELETE FROM ".self::TABLE_NAME."\n"
			    ." WHERE bb_id = ".$this->getDB()->quote($crs_bb);

		$this->getDB()->manipulate($query);
	}

	/**
	 * Add the crs ref id to block informations
	 *
	 * @param int 	$request_id 	id of the creation request
	 * @param int 	$crs_id 		crs ref id
	 */
	public function moveToCrsId($request_id, $crs_id) {
		$query = "UPDATE ".self::TABLE_NAME."\n"
				." SET crs_id = ".$this->getDB()->quote($crs_id)."\n"
				." WHERE request_id = ".$this->getDB()->quote($request_id);

		$this->getDB()->manipulate($query);
	}

	protected function getSelectStatement() {
		return "SELECT bb_id, crs_id, request_id, content, target\n"
			  ." FROM ".self::TABLE_NAME."\n";
	}

	protected function getDB() {
		return $this->gDB;
	}
}