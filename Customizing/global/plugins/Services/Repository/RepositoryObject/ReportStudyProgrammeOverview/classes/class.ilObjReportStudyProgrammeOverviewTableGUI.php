<?php
require_once "Services/CaTUIComponents/classes/class.catTableGUI.php";
require_once "Modules/StudyProgramme/classes/tables/class.ilIndividualPlanGUI.php";
require_once "Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php";

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilObjReportStudyProgrammeOverviewTableGUI extends catTableGUI
{
	public $styles = array(
							"table"		=> "tripleFullWidth"
					);

	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl;
		$this->g_ctrl = $ilCtrl;
		$this->link_tgt = new ilIndividualPlanGUI();
	}

	public function fillRow($a_set)
	{
		foreach ($a_set as $key => $value) {
			$this->g_ctrl->setParameter($this->link_tgt, "spRefId", $a_set['sp_ref_id']);
			$this->g_ctrl->setParameter($this->link_tgt, "user_id", $a_set['user_id']);
			$this->g_ctrl->setParameter($this->link_tgt, "assignment_id", $a_set['assignment']);
			$link = $this->g_ctrl->getLinkTargetByClass(array("ilIndividualPlanGUI"), "linkFollower");
			$this->g_ctrl->setParameter($this->link_tgt, "spRefId", null);
			$this->g_ctrl->setParameter($this->link_tgt, "user_id", null);
			$this->g_ctrl->setParameter($this->link_tgt, "assignment_id", null);
			$this->tpl->setVariable("HREF", $link);

			if ($key == "user_id" || $key == "sp_ref_id" || $key === "assignment") {
				continue;
			}
			if ($key == "firstname") {
				$this->tpl->setVariable("FIRSTNAME", $value);
				continue;
			}
			if ($key == "lastname") {
				$this->tpl->setVariable("LASTNAME", $value);
				continue;
			}

			$diff_arr = ["firstname", "lastname", "orgunit", "entry_date"];
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
			case ilStudyProgrammeProgress::STATUS_COMPLETED:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
			case ilStudyProgrammeProgress::STATUS_IN_PROGRESS:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
			case ilStudyProgrammeProgress::STATUS_ACCREDITED:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		}
	}
}
