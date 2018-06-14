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
			."WHERE adp_number = ".$this->db->quote($adp_number, "text");
		;

		$result = $this->db->query($query);

		return ($this->db->numRows($result) >= 1);
	}

	/**
	 * Create a table entries.
	 *
	 * @param 	string[] 	$adp_number
	 * @return 	void
	 */
	public function createEntries(array $adp_numbers)
	{
		$this->delete();
		foreach ($adp_numbers as $key => $apd_number) {
			$values = array(
				'id' => ['integer', $key],
				'adp_number' => ['text', $apd_number],
				'last_change' => ['text', date("Y-m-d")]
			);
			$this->db->insert(self::TABLENAME, $values);
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
		$this->db->query($query);
	}
}