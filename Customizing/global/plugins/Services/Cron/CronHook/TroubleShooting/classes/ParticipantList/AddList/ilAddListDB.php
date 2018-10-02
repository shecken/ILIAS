<?php

require_once __DIR__."/AddListDB.php";
require_once __DIR__."/../../DBTools.php";

class ilAddListDB implements AddListDB {
	use DBTools;

	const FINALIZED_STATE = 3;

	public function __construct($db)
	{
		$this->db = $db;
	}
}