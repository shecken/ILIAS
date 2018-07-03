<?php

use ILIAS\TMS\ReportUtilities\UDFWrapper;

/**
 * Provide centralized access to ILIAS' UDFs.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-traning.de>
 */
class ilUDFWrapper implements UDFWrapper {
	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 *@var ilUserDefinedFields
	 */
	protected $udf;


	public function __construct(\ilUserDefinedFields $udf, \ilDBInterface $db) {
		$this->udf = $udf;
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function getLUAVisibleFields() {
		return $this->udf->getLocalUserAdministrationDefinitions();
	}


	/**
	 * @inheritdoc
	 */
	public function getFieldValue($field_name, $usr_id) {
		assert('is_string($field_name)');
		assert('is_int($usr_id)');
		$f_id = $this->getFieldIdByName($field_name);
		//this is a hidden dependency. remove!
		$data = \ilUserDefinedData::lookupData([$usr_id], [$f_id]);
		return $data[$usr_id][$f_id];

		/*
	 	\ilUserDefinedData::lookupData($a_user_ids, $a_field_ids)

	 	$query = "SELECT * FROM udf_text ".
			"WHERE ".$ilDB->in('usr_id',$a_user_ids,false,'integer').' '.
			'AND '.$ilDB->in('field_id',$a_field_ids,false,'integer');
		$res = $ilDB->query($query);
	 	*/
	}

	/**
	 * Get the field's id by its name.
	 * @param string 	$name
	 * @return int
	 */
	protected function getFieldIdByName($name) {
		assert('is_string($name)');
		return $this->udf->fetchFieldIdFromName($name);
	}

	/**
	 * Get instance of DB.
	 * @throws \Exception
	 * @return \ilDBInterface
	 */
	protected function getDB() {
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}