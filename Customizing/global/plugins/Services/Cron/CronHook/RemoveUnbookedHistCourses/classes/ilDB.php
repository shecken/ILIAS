<?php

namespace CaT\Plugins\RemoveUnbookedHistCourses;

class ilDB
{

	/**
	 * @var \ilDB
	 */
	protected $db;

	public function __construct(\ilDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Get crs id of deleted courses withour members
	 *
	 * @return int[]
	 */
	public function getCourseToDelete()
	{
		$query = "SELECT hcrs.crs_id , count(hucrs.usr_id) AS member_count\n"
				." FROM hist_course hcrs\n"
				." LEFT JOIN hist_usercoursestatus hucrs ON hcrs.crs_id = hucrs.crs_id\n"
				."     AND hucrs.hist_historic = 0\n"
				."     AND hucrs.function = 'Mitglied'\n"
				." LEFT JOIN object_reference oref ON oref.obj_id = hcrs.crs_id\n"
				." WHERE hcrs.hist_historic = 0\n"
				."     AND oref.ref_id IS NULL\n"
				." GROUP BY hcrs.crs_id\n"
				." HAVING member_count = 0\n";

		$res = $this->getDB()->query($query);

		$ret = array();
		while ($row = $this->getDB()->fetchAssoc($res)) {
			$ret[] = (int)$row["crs_id"];
		}

		return $ret;
	}

	/**
	 * Marks every entry of course as historic.
	 *
	 * @param int 	$crs_id
	 *
	 * @return null
	 */
	public function markCourseHistoric($crs_id)
	{
		assert('is_int($crs_id)');

		$query = "UPDATE hist_course SET hist_historic = 1 WHERE crs_id = ".$this->getDB()->quote($crs_id, "integer");

		$this->getDB()->manipulate($query);
	}

	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}
