<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");

/**
 * Shows study programme and course of members VA Pass as a table
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilIndividualPlanDetailTableGUI extends catTableGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $g_lng;

	/**
	 * @var ilCtrl;
	 */
	protected $g_ctrl;

	public function __construct($a_parent_obj, $lp_children, $assignment_id, $user_id, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->setID("va_pass_member");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $lng, $ilCtrl;

		$this->g_lng = $lng;
		$this->g_ctrl = $ilCtrl;
		$this->user_id = $user_id;
		$this->assignment_id = $assignment_id;

		$this->success = '<img src="'.ilUtil::getImagePath("gev_va_pass_success_icon.png").'" />';
		$this->in_progress = '<img src="'.ilUtil::getImagePath("gev_va_pass_progress_icon.png").'" />';
		$this->faild = '<img src="'.ilUtil::getImagePath("gev_va_pass_failed_icon.png").'" />';
		$this->not_attemped = '<img src="'.ilUtil::getImagePath("gev_va_pass_not_attemped_icon.png").'" />';

		$this->confugireTable();
		$this->addColums();

		require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanEntry.php");
		foreach ($lp_children as $key => $value) {
			$entry = new ilIndividualPlanDetailEntry();
			$entry->setTitle($value->getTitle());
			$entry->setResult($this->getResultInfo($value));
		}

		$entries = array();

		$this->setData($entries);
	}

	public function fillRow($a_set)
	{
	}

	/**
	 * Configures the table settings
	 *
	 * @return null
	 */
	protected function confugireTable()
	{
		$this->setEnableTitle(true);
		$this->setExternalSegmentation(false);
		$this->setExternalSorting(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj, "view"));
		$this->setRowTemplate("tpl.individual_plan_detail_row.html", "Modules/StudyProgramme/");
		$this->useLngInTitle(false);
	}

	/**
	 * Add needed columns
	 *
	 * @return null
	 */
	protected function addColums()
	{
		$this->addColumn($this->g_lng->txt("gev_va_pass_step"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_accountable"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_finished"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_result"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_type_of_passed"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_status"));
		$this->addColumn($this->g_lng->txt("actions"));
	}

	protected function getLpStatusFor($obj_id, $user_id)
	{
		$lp = array();
		require_once("Services/Tracking/classes/class.ilLPStatus.php");
		$status = ilLPStatus::_lookupStatus($obj_id, $user_id);
		$lp["status"] = $status;
		$lp["finished"] = "";

		if ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
			$finished = ilLPStatus::_lookupStatusChanged;
			$lp["finished"] = $finished;
		}

		return $lp;
	}

	protected function getStatusIcon($status)
	{
		switch ($status) {
			case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
				return $this->not_attemped;
			case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
				return $this->in_progress;
			case ilLPStatus::LP_STATUS_COMPLETED_NUM:
				return $this->success;
			case ilLPStatus::LP_STATUS_FAILED_NUM:
				return $this->faild;
			default:
				return "";
		}
	}

	protected function getResultInfo($child)
	{
		foreach ($child->getLPChildren() as $key => $crs_ref) {
			$crs = ilObjectFactory::getInstanceByObjId($crs_ref->getTargetId());
			$sub_items = $crs->getSubItems();
		}
	}

	protected function getCrsUtils($crs_id)
	{
		return gevCourseUtils::getInstance($crs_id);
	}

	protected function targetIsSelflearning($crs_id)
	{
		return $this->getCrsUtils($crs_id)->isSelflearning();
	}

	protected function getStatDateOfTarget($crs_id)
	{
		return $this->getCrsUtils($crs_id)->getStartDate();
	}

	protected function currentFinishUntilIsLater($finish_until, $startdate)
	{
		return $finish_until->get(IL_CAL_UNIX) > $startdate->get(IL_CAL_UNIX);
	}
}
