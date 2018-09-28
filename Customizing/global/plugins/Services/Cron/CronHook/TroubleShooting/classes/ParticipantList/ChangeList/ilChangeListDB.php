<?php

require_once __DIR__."/ChangeListDB.php";
require_once __DIR__."/../../DBTools.php";

class ilChangeListDB implements ChangeListDB {
	use DBTools;

	public function __construct($db)
	{
		$this->db = $db;
	}
}