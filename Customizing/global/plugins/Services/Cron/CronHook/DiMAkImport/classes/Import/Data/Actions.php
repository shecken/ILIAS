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

	public function save($file_path)
	{
		$this->db->save($file_path);
	}

	public function checkAgendNumber($agent_number)
	{
		return $this->db->checkAgendNumber($agent_number);
	}
}