<?php
require_once "Services/CaTUIComponents/classes/class.catTableGUI.php";

class ilObjReportOverviewVATableGUI extends catTableGUI {
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
	}

	public function fillRow($a_set) {

		foreach ($a_set as $key => $value) {
			if($key == "user_id") { continue; }
			$this->tpl->setCurrentBlock("sp_column");
			$this->tpl->setVariable("VAL_SP", $value);
			$this->tpl->parseCurrentBlock();
		}
	}
}