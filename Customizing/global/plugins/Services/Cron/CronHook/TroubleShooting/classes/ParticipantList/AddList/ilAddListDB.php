<?php

require_once __DIR__."/AddListDB.php";
require_once __DIR__."/../../DBTools.php";

class ilAddListDB implements AddListDB {
	use DBTools;

	public function __construct($db)
	{
		$this->db = $db;
	}
}