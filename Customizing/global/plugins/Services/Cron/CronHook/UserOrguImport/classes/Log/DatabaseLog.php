<?php

namespace CaT\IliasUserOrguImport\Log;

class DatabaseLog implements Log
{

	protected $entries;

	const TABLE = 'xuoi_uoi_log';

	const ROW_ENTRY = 'entry';
	const ROW_KEY = 'id';
	const ROW_TIMESTAMP = 'timestamp';

	protected static $properties =
	[
		'orgu_id',
		'pnr'
	];


	public function __construct($db, $log)
	{
		$this->db = $db;
		$this->log = $log;
	}

	protected function filterMdForValidEntries(array $entry_md)
	{
		$valid = [];
		foreach (self::$properties as $prop) {
			if (isset($entry_md[$prop])) {
				$valid[$prop] = $entry_md[$prop];
			}
		}
		return $valid;
	}

	/**
	 * @inheritdoc
	 */
	public function lookupEntries(array $entry_md)
	{
		$entry_md = $this->filterMdForValidEntries($entry_md);
		assert('count($entry_md) > 0');
		$q = 'SELECT '.implode(',', self::$properties)
			.'		,'.self::ROW_ENTRY
			.'		,'.self::ROW_TIMESTAMP
			.'	FROM '.self::TABLE
			.'	WHERE TRUE';
		foreach ($entry_md as $row => $value) {
			$q .= '		AND '.$row.' = '.$this->db->quote($value, 'text');
		}
		$ret = [];
		$res = $this->db->query($q);
		while ($rec = $this->db->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function createEntry($entry, array $entry_md)
	{
		assert('is_string($entry)');
		$entry_md = $this->filterMdForValidEntries($entry_md);
		assert('count($entry_md) > 0');

		$id = $this->db->nextId(self::TABLE);
		$ts = time();
		$insert = [
			self::ROW_KEY => ['integer', $id],
			self::ROW_TIMESTAMP => ['integer', $ts],
			self::ROW_ENTRY => ['text', $entry]
			];
		foreach ($entry_md as $row => $value) {
			$insert[$row] =  ['text',$value];
		}

		$this->log->write('UserOrguImport: '.self::arrayToString($entry_md).': '.$entry);

		$this->db->insert(
			self::TABLE,
			$insert
		);
	}

	/**
	 * Format an associative array of string to a string
	 * without loss of information.
	 *
	 * @param	string[string]	$data
	 */
	public static function arrayToString(array $data)
	{
		$aux = [];
		foreach ($data as $key => $value) {
			$aux[] = $key.':'.$value;
		}
		return implode(';', $aux);
	}
}
