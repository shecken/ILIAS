<?php

/**
 * Database class. Handles jobnumber methods.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class gevJobnumberDB
{
	const TABLENAME = "jobnumber_import";

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
	 * Get a db tupel by jobnumber.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	array
	 */
	public function getEntryByJobnumber($jobnumber)
	{
		assert('is_string($jobnumber)');

		$query =
			 "SELECT id, jobnumber, last_change, vms_text, agent_status".PHP_EOL
			."FROM ".self::TABLENAME.PHP_EOL
			."WHERE jobnumber = ".$this->db->quote($jobnumber, "text").PHP_EOL
		;
		$result = $this->db->query($query);

		return $this->db->fetchAssoc($result);
	}

	/**
	 * Check for an jobnumber.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	bool
	 */
	public function checkForJobnumber($jobnumber)
	{
		assert('is_string($jobnumber)');

		$query =
			 "SELECT id".PHP_EOL
			."FROM ".self::TABLENAME.PHP_EOL
			."WHERE jobnumber = ".$this->db->quote($jobnumber, "text").PHP_EOL
		;

		$result = $this->db->query($query);

		return ($this->db->numRows($result) >= 1);
	}

	/**
	 * Get the status of the agent.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	int
	 */
	public function getAgentStatus($jobnumber)
	{
		assert('is_string($jobnumber)');

		$query =
			 "SELECT agent_status".PHP_EOL
			."FROM ".self::TABLENAME.PHP_EOL
			."WHERE jobnumber = ".$this->db->quote($jobnumber, "text").PHP_EOL
		;
		$result = $this->db->query($query);

		if ($this->db->numRows($result) < 1) {
			return -1;
		}

		$row = $this->db->fetchAssoc($result);

		return $row['agent_status'];
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
				'jobnumber' => ['text', $key],
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