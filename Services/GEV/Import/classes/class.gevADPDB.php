<?php

/**
 * Database class. Handles adp methods.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class gevADPDB
{
	const TABLENAME = "adp_import";

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * Constructor of the class gevADPDB.
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Get a db tupel by adp_number.
	 *
	 * @param 	string 	$adp_number
	 * @return 	array
	 */
	public function getEntryByAdpNumber($adp_number)
	{
		assert('is_string($adp_number)');

		$query =
			 "SELECT id, adp_number, last_change, vms_text, agent_status".PHP_EOL
			."FROM ".self::TABLENAME.PHP_EOL
			."WHERE adp_number = ".$this->db->quote($adp_number, "text").PHP_EOL
		;
		$result = $this->db->query($query);

		return $this->db->fetchAssoc($result);
	}

	/**
	 * Check for an adp_number.
	 *
	 * @param 	string 	$adp_number
	 * @return 	bool
	 */
	public function checkForAdpNumber($adp_number)
	{
		assert('is_string($adp_number)');

		$query =
			 "SELECT id".PHP_EOL
			."FROM ".self::TABLENAME.PHP_EOL
			."WHERE adp_number = ".$this->db->quote($adp_number, "text").PHP_EOL
		;

		$result = $this->db->query($query);

		return ($this->db->numRows($result) >= 1);
	}

	/**
	 * Get the status of the agent.
	 *
	 * @param 	string 	$adp_number
	 * @return 	int
	 */
	public function getAgentStatus($adp_number)
	{
		assert('is_string($adp_number)');

		$query =
			 "SELECT agent_status".PHP_EOL
			."FROM ".self::TABLENAME.PHP_EOL
			."WHERE adp_number = ".$this->db->quote($adp_number, "text").PHP_EOL
		;
		$result = $this->db->query($query);

		$row = $this->db->fetchAssoc($result);

		return (int)$row['agent_status'];
	}

	/**
	 * Create a table entries.
	 *
	 * @param 	string[] 	$values
	 * @return 	void
	 */
	public function createEntries(array $values)
	{
		$id = 0;
		$this->delete();
		global $ilLog;
		$ilLog->dump($values);
		foreach ($values as $key => $value) {
			$result = array(
				'id' => ['integer', $id],
				'adp_number' => ['text', $key],
				'last_change' => ['text', date("Y-m-d")],
				'vms_text' => ['text', $value['vms_text']],
				'agent_status' => ['integer', $value['agent_status']]
			);
			$this->db->insert(self::TABLENAME, $result);
			$id++;
		}
	}

	/**
	 * Delete all db entries.
	 *
	 * @return 	void
	 */
	private function delete()
	{
		$query = "DELETE FROM ".self::TABLENAME;
		$this->db->manipulate($query);
	}
}