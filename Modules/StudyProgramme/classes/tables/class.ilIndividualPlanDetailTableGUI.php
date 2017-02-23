<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanDetailTable.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php");

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
	 * @var ilObjUser
	 */
	protected $g_user;

	/**
	 * @var ilSetting;
	 */
	protected $settings;

	public function __construct($a_parent_obj, $lp_children, $assignment_id, $user_id, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->setID("va_pass_member");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $lng, $ilCtrl, $ilDB, $ilUser;

		$this->g_lng = $lng;
		$this->g_ctrl = $ilCtrl;
		$this->g_db = $ilDB;
		$this->g_user = $ilUser;
		$this->user_id = $user_id;
		$this->user = ilObjectFactory::getInstanceByObjId($this->user_id);
		$this->mass_storage = new ilManualAssessmentMembersStorageDB($this->g_db);
		$this->assignment_id = $assignment_id;

		$this->success = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		$this->in_progress = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		$this->faild = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
		$this->not_attemped = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-neutral.png").'" />';

		$this->settings = new ilSetting("gev");
		$this->obj = new ilIndividualPlanDetailTable();

		$this->confugireTable();
		$this->addColums();
		$entries = array();
		require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanDetailEntry.php");
		foreach ($lp_children as $lp_current_child_key => $lp_child) {
			$entry = new ilIndividualPlanDetailEntry();
			$entry->setStudyProgramme($lp_child);
			$entry->setCourseWhereUserIsMember($this->getCourseWhereUserIsMember($lp_child));
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
		// TODO: There needs to be a link here to the course where the user is member
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
		$finish_until = $entry->getFinished();
		if ($finish_until && $entry->getStatus() == 2) {
			$this->tpl->setVariable("FINISHED", $finish_until->get(IL_CAL_FKT_DATE, "d.m.Y"));
		} else {
			$this->tpl->setVariable("FINISHED","-");
		}
		$this->tpl->setVariable("ACTION", $this->getActionMenu($entry));
	}

	public function getActionMenu(\ilIndividualPlanDetailEntry $entry) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$l = new \ilAdvancedSelectionListGUI();
		$l->setAsynch(false);
		$l->setAsynchUrl(true);
		$l->setListTitle($this->g_lng->txt("actions"));
		$l->setId($entry->getStudyProgramme()->getRefId());
		$l->setSelectionHeaderClass("small");
		$l->setItemLinkClass("xsmall");
		$l->setLinksMode("il_ContainerItemCommand2");
		$l->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$l->setUseImages(false);
		$l->setAdditionalToggleElement("err_ids".$err_ids, "ilContainerListItemOuterHighlight");

		foreach ($this->getActionMenuItems($entry) as $item) {
			$l->addItem($item["title"],"",$item["link"],"","","_blank");
		}
		return $l->getHTML();
	}

	public function getActionMenuItems(\ilIndividualPlanDetailEntry $entry) {
		$crs = $entry->getCourseWhereUserIsMember($entry->getStudyProgramme());
		if ($crs === null) {
			return [];
		}
		$crs_utils = gevCourseUtils::getInstanceByObj($crs);
		$items = [];
		if ($crs_utils->isPraesenztraining() || $crs_utils->isWebinar() || $crs_utils->isVirtualTraining()) {
		}
		else if ($crs_utils->isCoaching()) {
			$mass = $this->getManualAssessmentIn($crs);
			if ($mass) {
				// Make the user a member if he currently is not one.
				$members = $this->mass_storage->loadMembers($mass);
				if (!$members->userAllreadyMember($this->user)) {
					$members = $members->withAdditionalUser($this->user);
					$members->updateStorageAndRBAC($this->mass_storage, $mass->accessHandler());
				}
				if ($this->mayViewManualAssessmentRecord($mass)) {
					$items[] =
						["title" => "Gesprächsnotizen einsehen",
						 "link" => $this->manualAssessmentRecordViewLink($mass)];
				}
			}
		}
		return $items;
	}

	/**
	 * Check if a user may view the record of the user on the manual assessment.
	 *
	 * @param	\ilObjManualAssessment	$mass
	 * @return	bool
	 */
	protected function mayViewManualAssessmentRecord(\ilObjManualAssessment $mass) {
		$mass_member = $this->mass_storage->loadMember($mass, $this->user);
		if (!$mass_member->finalized()) {
			// No finalized record, no record.
			return false;
		}
		return true;
	}

	/**
	 * Get the link to view the manual assessment record for the user.
	 *
	 * @param	\ilObjManualAssessment	$mass
	 * @return	bool
	 */
	protected function manualAssessmentRecordViewLink(\ilObjManualAssessment $mass) {
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "ref_id", $mass->getRefId());
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "usr_id", $this->user_id);
		$link = $this->g_ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilObjManualAssessmentGUI", "ilManualAssessmentMembersGUI", "ilManualAssessmentMemberGUI"], "view");
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "ref_id", null);
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "usr_id", null);
		return $link;
	}

	/**
	 * Configures the table settings
	 *
	 * @return null
	 */
	// TODO: There is spelling mistake hidden in this name....
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
		$this->addColumn($this->g_lng->txt("prg_step"));
		$this->addColumn($this->g_lng->txt("prg_accountable"));
		$this->addColumn($this->g_lng->txt("prg_date"));
		$this->addColumn($this->g_lng->txt("prg_result"));
		$this->addColumn($this->g_lng->txt("prg_type_of_passed"));
		$this->addColumn($this->g_lng->txt("prg_status"));
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

	/**
	 * Get the course the user passed to complete the given SP.
	 *
	 * If no course lead to passing returns null.
	 *
	 * @param	ilObjStudyProgramme		$sp
	 * @return	ilObjCourse|null
	 */
	protected function getPassedCourse(\ilObjStudyProgramme $sp) {
		$progress = $sp->getProgressForAssignment($this->assignment_id);
		if(!$progress->isSuccessful() || $progress->isAccredited()) {
			return null;
		}
		// This should be a course as we are in the LP-Mode study programmes and
		// the node was not accredited.
		$crs_id = $progress->getCompletionBy();
		$crs_ref_id = gevObjectUtils::getRefId($crs_id);
		if ($crs_ref_id == null) {
			throw new \ilException("Cannot find ref_id for course '$crs_id'");
		}
		return ilObjectFactory::getInstanceByRefId($crs_ref->getTargetRefId());
	}

	/**
	 * Get the first course in this SP where the user is member.
	 *
	 * TODO: This just returns on the first course that is found. A more sophisticated
	 * choice would be great.
	 *
	 * If no course is found returns null.
	 *
	 * @param	ilObjStudyProgramme	$sp
	 * @param	ilObjCourse
	 */
	protected function getCourseWhereUserIsMember(\ilObjStudyProgramme $sp) {
		assert('$sp->getLPMode() == \ilStudyProgramme::MODE_LP_COMPLETED');
		foreach ($sp->getLPChildren() as $crs_ref) {
			$crs = ilObjectFactory::getInstanceByRefId($crs_ref->getTargetRefId());
			$crs_utils = gevCourseUtils::getInstanceByObj($crs);
			if ($crs_utils->isMember($this->user_id)) {
				return $crs;
			}
		}
		return null;
	}

	/**
	 * Get the first Manual Assessment in the course.
	 *
	 * TODO: This just returns the first manual assessment that is found. A more
	 * sophisticated choice would be great.
	 *
	 * @param	ilObjCourse	$crs
	 * @return	ilObjManualAssessment
	 */
	protected function getManualAssessmentIn(\ilObjCourse $crs) {
		$sub_items = $crs->getSubItems();
		if (array_key_exists("mass", $sub_items)) {
			$item = array_shift($sub_items["mass"]);
			return ilObjectFactory::getInstanceByRefId($item['ref_id']);
		}
		return null;
	}

	protected function getResultInfo(\ilObjStudyProgramme $child)
	{
		$maybe_crs = $this->getPassedCourse($child);
		if ($maybe_crs === null) {
			return "-";
		}

		$sub_items = $crs->getSubItems();
		if (array_key_exists("mass", $sub_items)) {
			foreach($sub_items['mass'] as $item) {
				$ia = ilObjectFactory::getInstanceByRefId($item['ref_id']);
				// TODO: This just returns the result for the first member. That
				// is not what we want.
				$members = $ia->loadMembers();
				foreach ($members as $member) {
					return $member['record'] . "<br>";
				}
			}
		}
	}
}
