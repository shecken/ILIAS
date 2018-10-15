<?php

namespace CaT\Plugins\DiMAkImport\Import\Data;

class Actions {
	public function __construct(DB $db)
	{
		$this->db = $db;
	}

	public function truncate()
	{
		$this->db->truncate();
	}

	public function save($agent_number)
	{
		assert('is_int($agent_number)');
		$this->db->save($agent_number);
	}

	public function checkAgendNumber($agent_number)
	{
		assert('is_int($agent_number)');
		return $this->db->checkAgendNumber($agent_number);
	}
}