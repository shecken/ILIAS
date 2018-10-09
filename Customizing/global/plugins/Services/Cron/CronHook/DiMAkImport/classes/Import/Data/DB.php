<?php

namespace CaT\Plugins\DiMAkImport\Import\Data;

interface DB {
	public function truncate();
	public function save($agent_number);
	public function checkAgendNumber($agent_number);
}