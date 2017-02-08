<?php
require_once "Services/CaTUIComponents/classes/class.catTableGUI.php";

class ilObjReportOverviewVATableGUI extends catTableGUI
{
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
	}

	public function fillRow($a_set)
	{

		foreach ($a_set as $key => $value) {
			if ($key == "user_id") {
				continue;
			}
			$diff_arr = ["user_id", "firstname", "lastname", "orgunit", "entry_date"];
			if (!in_array($key, $diff_arr)) {
				$value = $this->statusToImage($value);
			}
			$this->tpl->setCurrentBlock("sp_column");
			$this->tpl->setVariable("VAL_SP", $value);
			$this->tpl->parseCurrentBlock();
		}
	}

	protected function statusToImage($status)
	{
		switch ($status) {
			case ilLPStatus::LP_STATUS_COMPLETED_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
			case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
			case ilLPStatus::LP_STATUS_FAILED_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
			case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-neutral.png").'" />';
		}
	}
}
