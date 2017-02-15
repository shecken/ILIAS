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

	/**
	 * @var ilDB;
	 */
	protected $g_db;

	/**
	 * @var ilSetting;
	 */
	protected $g_settings;

	public function __construct($a_parent_obj, $lp_children, $assignment_id, $user_id, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->setID("va_pass_member");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $lng, $ilCtrl, $ilDB,  $ilSetting;

		$this->g_lng = $lng;
		$this->g_ctrl = $ilCtrl;
		$this->g_db = $ilDB;
		$this->g_settings =  $ilSetting;
		$this->user_id = $user_id;
		$this->assignment_id = $assignment_id;

		$this->success = '<img src="'.ilUtil::getImagePath("gev_va_pass_success_icon.png").'" />';
		$this->in_progress = '<img src="'.ilUtil::getImagePath("gev_va_pass_progress_icon.png").'" />';
		$this->faild = '<img src="'.ilUtil::getImagePath("gev_va_pass_failed_icon.png").'" />';
		$this->not_attemped = '<img src="'.ilUtil::getImagePath("gev_va_pass_not_attemped_icon.png").'" />';

		$this->confugireTable();
		$this->addColums();

		$entries = array();
		require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanDetailEntry.php");
		foreach ($lp_children as $lp_current_child_key => $lp_child) {
			$entry = new ilIndividualPlanDetailEntry();
			$entry->setTitle($lp_child->getTitle());
			//$entry->setResult($this->getResultInfo($value));
			$entry->setAccountable($this->getAccountable($lp_child->getId(), $this->getVAPassAccountableFieldId()));
			$entry->setTypeOfPass("pass");
			$lp_status = $this->getLpStatusFor($lp_child->getId(), $this->user_id);
			$entry->setStatus($lp_status["status"]);
			$entry->setFinished($lp_status["finished"]);

			// $finish_until = $this->getCourseStartNextSP($lp_children, $lp_current_child_key + 1);
			// if ($finish_until) {
			// 	$entry->setFinishUntil($finish_until);
			// }


			$entries[] = $entry;
		}
		// var_dump($entries);exit;
		$this->setData($entries);
	}

	public function fillRow($a_set)
	{
		$this->tpl->setVariable("STEPNAME", $a_set->getTitle());
		$this->tpl->setVariable("ACCOUNTABLE", $a_set->getAccountable());
		$this->tpl->setVariable("RESULT", $a_set->getResult());
		$this->tpl->setVariable("TYPE_OF_PASSED", $a_set->getTypeOfPass());
		$this->tpl->setVariable("STATUS", $this->getStatusIcon($a_set->getStatus()));
		//$this->tpl->setVariable("FINISHED", $finish_until->get(IL_CAL_FKT_DATE, "d.m.Y"));
		$finish_until = $a_set->getFinishUntil();
		if ($finish_until) {
			$this->tpl->setVariable("FINISHED", $finish_until->get(IL_CAL_FKT_DATE, "d.m.Y"));
		} else {
			$this->tpl->setVariable("FINISHED","-");
		}
	}

	// public function fillRow($a_set)
	// {
	// 	$this->g_ctrl->setParameter($this->parent_obj, "spRefId", $a_set->getRefId());
	// 	$this->g_ctrl->setParameter($this->parent_obj, "user_id", $this->user_id);
	// 	$this->g_ctrl->setParameter($this->parent_obj, "assignment_id", $this->assignment_id);
	// 	$link = $this->g_ctrl->getLinkTarget($this->parent_obj, "view");
	// 	$this->g_ctrl->setParameter($this->parent_obj, "spRefId", null);
	// 	$this->g_ctrl->setParameter($this->parent_obj, "user_id", null);
	// 	$this->g_ctrl->setParameter($this->parent_obj, "assignment_id", null);
	// 	$this->tpl->setVariable("HREF", $link);

	// 	$this->tpl->setVariable("TITLE", $a_set->getTitle());
	// 	$this->tpl->setVariable("STATUS", $this->parent_obj->getStatusIcon($a_set->getStatus()));
	// 	$this->tpl->setVariable("FINISHED", $a_set->getFinished());

	// 	$finish_until = $a_set->getFinishUntil();
	// 	if ($finish_until) {
	// 		$this->tpl->setVariable("FINISH_UNTIL", $finish_until->get(IL_CAL_FKT_DATE, "d.m.Y"));
	// 	}

	// 	$this->g_ctrl->setParameter($this->parent_obj, "selectedRefId", null);
	// }

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
		$this->addColumn($this->g_lng->txt("gev_va_pass_date"));
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

	protected function getStartDateOfTarget($crs_id)
	{
		return $this->getCrsUtils($crs_id)->getStartDate();
	}

	protected function currentFinishUntilIsLater($finish_until, $startdate)
	{
		return $finish_until->get(IL_CAL_UNIX) > $startdate->get(IL_CAL_UNIX);
	}

	protected function getVAPassAccountableFieldId()
	{
		var_dump($this->g_settings->get(gevSettings::VA_PASS_ACCOUNTABLE_FIELD_ID));exit;
		return $this->g_settings->get(gevSettings::VA_PASS_ACCOUNTABLE_FIELD_ID);
	}

	protected function getGevSettings()
	{
		var_dump($this->g_settings->get(gevSettings::getVAPassPassingTypeFieldId));
	}

	protected function getAccountable($obj_id, $field_id) {
		$query = "SELECT value \n"
				."FROM adv_md_values_text\n"
				."WHERE obj_id = " . $this->g_db->quote($obj_id, "integer") ."\n"
				."   AND field_id = " . $this->g_db->quote($field_id, "integer");
		$res = $this->g_db->query($query);
		return $this->g_db->fetchAssoc($res)['value'];
	}
}
