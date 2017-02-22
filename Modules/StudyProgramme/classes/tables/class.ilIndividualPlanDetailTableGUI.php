<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanDetailTable.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");

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
	protected $settings;

	public function __construct($a_parent_obj, $lp_children, $assignment_id, $user_id, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->setID("va_pass_member");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $lng, $ilCtrl, $ilDB;

		$this->g_lng = $lng;
		$this->g_ctrl = $ilCtrl;
		$this->g_db = $ilDB;
		$this->user_id = $user_id;
		$this->assignment_id = $assignment_id;

		$this->success = '<img src="'.ilUtil::getImagePath("gev_va_pass_success_icon.png").'" />';
		$this->in_progress = '<img src="'.ilUtil::getImagePath("gev_va_pass_progress_icon.png").'" />';
		$this->faild = '<img src="'.ilUtil::getImagePath("gev_va_pass_failed_icon.png").'" />';
		$this->not_attemped = '<img src="'.ilUtil::getImagePath("gev_va_pass_not_attemped_icon.png").'" />';

		$this->settings = new ilSetting("gev");
		$this->obj = new ilIndividualPlanDetailTable();

		$this->confugireTable();
		$this->addColums();
		$entries = array();
		require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanDetailEntry.php");
		foreach ($lp_children as $lp_current_child_key => $lp_child) {
			$entry = new ilIndividualPlanDetailEntry();
			$entry->setTitle($lp_child->getTitle());
			$entry->setAccountable($this->obj->getAccountable($lp_child->getId(), $this->obj->getVAPassAccountableFieldId()));
			$type = $this->obj->getAccountable($lp_child->getId(), $this->obj->getVAPassPassingTypeFieldId());
			$entry->setTypeOfPass($type);
			if($type == "Manueller Eintrag") {
				$entry->setResult($this->getResultInfo($lp_child));
			} else {
				$entry->setResult("-");
			}
			$lp = $this->obj->getLPStatus($lp_child->getId(), $this->user_id);
			$entry->setStatus($lp['status']);
			$date = new ilDateTime($lp['last_change'], IL_CAL_DATETIME);
			$entry->setFinished($date);

			$entries[] = $entry;
		}
		$this->setData($entries);
	}

	public function fillRow(\ilIndividualPlanDetailEntry $entry)
	{
		$this->tpl->setVariable("STEPNAME", $entry->getTitle());
		if ($entry->getAccountable()) {
			$this->tpl->setVariable("ACCOUNTABLE", $entry->getAccountable());
		} else {
			$this->tpl->setVariable("ACCOUNTABLE", "-");
		}
		$this->tpl->setVariable("RESULT", $entry->getResult());
		if ($entry->getTypeOfPass()) {
			$this->tpl->setVariable("TYPE_OF_PASSED", $entry->getTypeOfPass());
		} else {
			$this->tpl->setVariable("TYPE_OF_PASSED", "-");
		}
		$this->tpl->setVariable("STATUS", $this->getStatusIcon($entry->getStatus()));
		$finish_until = $entry->getFinishUntil();
		if ($finish_until && $entry->getStatus() == 3) {
			$this->tpl->setVariable("FINISHED", $finish_until->get(IL_CAL_FKT_DATE, "d.m.Y"));
		} else {
			$this->tpl->setVariable("FINISHED","-");
		}
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
		$this->addColumn($this->g_lng->txt("gev_va_pass_date"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_result"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_type_of_passed"));
		$this->addColumn($this->g_lng->txt("gev_va_pass_status"));
		$this->addColumn($this->g_lng->txt("actions"));
	}

	protected function getStatusIcon($status)
	{
		switch ($status) {
			case 1:
				return $this->not_attemped;
			case 3:
				return $this->in_progress;
			case 2:
				return $this->success;
			case 4:
				return $this->faild;
			default:
				return "";
		}
	}

	protected function getResultInfo($child)
	{
		$progress = $child->getProgressForAssignment($this->assignment_id);
		if(!$progress->isSuccessful() || $progress->isAccredited()) {
			return "-";
		}
		// TODO: we need the actually completed course here, not _ANY_ course.
		foreach ($child->getLPChildren() as $key => $crs_ref) {
			$crs = ilObjectFactory::getInstanceByRefId($crs_ref->getTargetRefId());
			$sub_items = $crs->getSubItems();
			if (array_key_exists("mass", $sub_items)) {
				foreach($sub_items['mass'] as $item) {
					$ia = ilObjectFactory::getInstanceByRefId($item['ref_id']);
					$members = $ia->loadMembers();
					foreach ($members as $member) {
						return $member['record'] . "<br>";
					}
				}
			}
		}
	}
}
