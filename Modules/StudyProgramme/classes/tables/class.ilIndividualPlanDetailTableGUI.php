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
	const STATUS_NOT_ATTEMPTED = 1;
	const STATUS_IN_PROGRESS = 3;
	const STATUS_SUCCESS = 2;

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
		$this->g_user_id = $this->g_user->getId();
		$this->user_id = $user_id;
		$this->user = ilObjectFactory::getInstanceByObjId($this->user_id);
		$this->mass_storage = new ilManualAssessmentMembersStorageDB($this->g_db);
		$this->assignment_id = $assignment_id;

		$this->g_lng->loadLanguageModule("mass");

		$this->success = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		$this->in_progress = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
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
			if ($type == "Manueller Eintrag") {
				$entry->setResult($this->getResultInfo($lp_child));
			} else {
				$entry->setResult("-");
			}
			list($status, $date) = $this->getStatusAndDate($lp_child);
			$entry->setStatus($status);
			$entry->setFinished($date);
			$entries[] = $entry;
		}
		$this->setData($entries);
	}

	public function fillRow(\ilIndividualPlanDetailEntry $entry)
	{
		$this->tpl->setVariable("STEPNAME", $entry->getTitle());
		$crs = $entry->getCourseWhereUserIsMember();
		if($crs != null) {
			$this->g_ctrl->setParameterByClass("ilObjCourseGUI", "ref_id", $crs->getRefId());
			$link = $this->g_ctrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjCourseGUI"), "view");
			$this->tpl->setVariable("LINK", $link);
			$this->g_ctrl->clearParametersByClass("ilObjCourseGUI");
		}

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
			$this->tpl->setVariable("FINISHED", "-");
		}
		$this->tpl->setVariable("ACTION", $this->getActionMenu($entry));
	}

	protected function getStatusAndDate(\ilObjStudyProgramme $sp)
	{
		$progress = $sp->getProgressForAssignment($this->assignment_id);
		if ($progress->isAccredited() || $progress->isSuccessful()) {
			$maybe_crs = $this->getPassedCourse($sp);
			if ($maybe_crs) {
				$maybe_ia = $this->getManualAssessmentWhereUserIsMemberIn($maybe_crs);
			} else {
				$maybe_ia = null;
			}
			if ($maybe_ia) {
				$record = $maybe_ia->membersStorage()->loadMember($maybe_ia, $this->user);
				$date = $record->eventTime();
			}
			if (!$date) {
				$lp = $this->obj->getLPStatus($sp->getId(), $this->user_id);
				$date = new ilDateTime($lp['last_change'], IL_CAL_DATETIME);
			}
			return [self::STATUS_SUCCESS, $date];
		}
		$crs = $this->getCourseWhereUserIsMember($sp);
		if ($crs === null) {
			return [self::STATUS_NOT_ATTEMPTED, null];
		}
		return [self::STATUS_IN_PROGRESS, null];
	}

	public function getActionMenu(\ilIndividualPlanDetailEntry $entry)
	{
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
			$l->addItem($item["title"], "", $item["link"], "", "", "_blank");
		}
		return $l->getHTML();
	}

	public function getActionMenuItems(\ilIndividualPlanDetailEntry $entry)
	{
		$crs = $entry->getCourseWhereUserIsMember($entry->getStudyProgramme());
		if ($crs === null) {
			return [];
		}
		$crs_utils = gevCourseUtils::getInstanceByObj($crs);
		$items = [];
		if ($crs_utils->isPraesenztraining() || $crs_utils->isWebinar() || $crs_utils->isVirtualTraining()) {
			$items = $this->maybeAddDownloadMemberlistTo($items, $crs_utils);
			$items = $this->maybeAddViewBookingsTo($items, $crs_utils);
			$items = $this->maybeAddViewMailingTo($items, $crs_utils);
			$items = $this->maybeAddDownloadSignatureListTo($items, $crs_utils);
			$items = $this->maybeAddDownloadParticipantsListTo($items, $crs_utils);
			$items = $this->maybeAddSetParticipationStatusTo($items, $crs_utils);
		} elseif ($crs_utils->isCoaching()) {
			$mass = $this->getManualAssessmentIn($crs);
			if ($mass) {
				// Make the user a member if he currently is not one.
				$members = $this->mass_storage->loadMembers($mass);
				if (!$members->userAllreadyMember($this->user)) {
					$members = $members->withAdditionalUser($this->user);
					$members->updateStorageAndRBAC($this->mass_storage, $mass->accessHandler());
				}

				$items = $this->maybeAddViewRecordTo($items, $mass);
				$items = $this->maybeAddEditRecordTo($items, $mass);
			}
		}
		return $items;
	}

	protected function maybeAddViewRecordTo(array $items, ilObjManualAssessment $mass)
	{
		$member = $mass->membersStorage()->loadMember($mass, $this->user);
		$ex_id = $member->examinerId();
		$access = $mass->accessHandler();

		$finalized = $member->finalized();
		$edited_by_other = $ex_id != $this->g_user_id && 0 !== (int)$set[ilManualAssessmentMembers::FIELD_EXAMINER_ID];
		$may_view = $access->mayViewUserIn($this->user_id, $mass, true);
		$may_grade = $access->mayGradeUserIn($this->user_id, $mass, true);

		if (($finalized && !$edited_by_other && $may_grade) || ($finalized && $may_view)) {
			$items[] =
				["title" => $this->g_lng->txt("mass_view_record"),
				 "link" => $this->manualAssessmentRecordViewLink($mass)];
		}
		return $items;
	}

	protected function maybeAddEditRecordTo(array $items, ilObjManualAssessment $mass)
	{
		$member = $mass->membersStorage()->loadMember($mass, $this->user);
		$ex_id = $member->examinerId();

		$finalized = $member->finalized();
		$edited_by_other = $ex_id != $this->g_user_id && 0 !== (int)$set[ilManualAssessmentMembers::FIELD_EXAMINER_ID];
		$may_grade = $mass->accessHandler()->mayGradeUserIn($this->user_id, $mass, true);

		if (!$finalized && !$edited_by_other && $may_grade) {
			$items[] =
				["title" => $this->g_lng->txt("mass_edit_record"),
				 "link" => $this->manualAssessmentRecordEditLink($mass)];
		}
		return $items;
	}

	protected function maybeAddDownloadMemberlistTo(array $items, $crs_utils)
	{
		if ($crs_utils->userHasPermissionTo($this->g_user_id, gevSettings::LOAD_MEMBER_LIST)) {
			$this->g_ctrl->setParameterByClass("gevMemberListDeliveryGUI", "ref_id", $crs_utils->getRefId());
			$items[] = ['title' => $this->g_lng->txt("download_memberlist_ip"),
						'link' => $this->g_ctrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "trainer")];
			$this->g_ctrl->clearParametersByClass("gevMemberListDeliveryGUI");
		}
		return $items;
	}

	protected function maybeAddViewBookingsTo(array $items, $crs_utils)
	{
		if ($crs_utils->canViewBookings($this->g_user_id)) {
			$this->g_ctrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $crs_utils->getRefId());
			$items[] = ['title' => $this->g_lng->txt('view_bookings_ip'),
						'link' => $this->g_ctrl->getLinkTargetByClass(["ilCourseBookingGUI", "ilCourseBookingAdminGUI"])];
			$this->g_ctrl->clearParametersByClass("ilCourseBookingGUI");
		}
		return $items;
	}

	protected function maybeAddViewMailingTo(array $items, $crs_utils)
	{
		if ($crs_utils->userHasPermissionTo($this->g_user_id, gevSettings::VIEW_MAILING)) {
			$this->g_ctrl->setParameterByClass("gevTrainerMailHandlingGUI", "crs_id", $crs_utils->getId());
			$items[] = ['title' => $this->g_lng->txt('view_mailing_ip'),
						'link' => $this->g_ctrl->getLinkTargetByClass("gevTrainerMailHandlingGUI", "showLog")];
			$this->g_ctrl->clearParametersByClass("gevTrainerMailHandlingGUI");
		}

		return $items;
	}

	protected function maybeAddDownloadSignatureListTo(array $items, $crs_utils)
	{
		if ($crs_utils->userHasPermissionTo($this->g_user_id, gevSettings::LOAD_SIGNATURE_LIST)) {
			$this->g_ctrl->setParameterByClass("gevMemberListDeliveryGUI", "ref_id", $crs_utils->getRefId());
			$items[] = ['title' => $this->g_lng->txt('signature_list_ip'),
						'link' => $this->g_ctrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "download_signature_list")];
			$this->g_ctrl->clearParametersByClass("gevMemberListDeliveryGUI");
		}
		return $items;
	}

	protected function maybeAddDownloadParticipantsListTo(array $items, $crs_utils)
	{
		if ($crs_utils->userHasPermissionTo($this->g_user_id, gevSettings::LOAD_SIGNATURE_LIST)
			&& ilParticipationStatus::getInstance($crs_utils->getCourse())->getAttendanceList()) {
			$this->g_ctrl->setParameterByClass('ilIndividualPlanGUI', "crsrefid", $crs_utils->getRefId());
			$items[] = ['title' => $this->g_lng->txt('participants_list_ip'),
						'link' => $this->g_ctrl
										->getLinkTarget($this->parent_obj, "viewAttendanceList")];
			$this->g_ctrl->setParameterByClass('ilIndividualPlanGUI', "crsrefid", null);
		}
		return $items;
	}

	protected function maybeAddSetParticipationStatusTo(array $items, $crs_utils)
	{
		if ($crs_utils->canModifyParticipationStatus($this->g_user_id)) {
			$this->g_ctrl->setParameterByClass('ilIndividualPlanGUI', "target_ref_id", $crs_utils->getRefId());
			$items[] = ['title' => $this->g_lng->txt('p_status_ip'),
						'link' => $this->g_ctrl
										->getLinkTarget($this->parent_obj, 'participationStatus')];
			$this->g_ctrl->setParameterByClass('ilIndividualPlanGUI', "target_ref_id", null);
		}
		return $items;
	}

	/**
	 * Get the link to view the manual assessment record for the user.
	 *
	 * @param	\ilObjManualAssessment	$mass
	 * @return	string
	 */
	protected function manualAssessmentRecordViewLink(\ilObjManualAssessment $mass)
	{
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "ref_id", $mass->getRefId());
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "usr_id", $this->user_id);
		$link = $this->g_ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilObjManualAssessmentGUI", "ilManualAssessmentMembersGUI", "ilManualAssessmentMemberGUI"], "view");
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "ref_id", null);
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "usr_id", null);
		return $link;
	}

	/**
	 * Get the link to edit the manual assessment record for the user.
	 *
	 * @param	\ilObjManualAssessment	$mass
	 * @return	string
	 */
	protected function manualAssessmentRecordEditLink(\ilObjManualAssessment $mass)
	{
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "ref_id", $mass->getRefId());
		$this->g_ctrl->setParameterByClass("ilManualAssessmentMemberGUI", "usr_id", $this->user_id);
		$link = $this->g_ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilObjManualAssessmentGUI", "ilManualAssessmentMembersGUI", "ilManualAssessmentMemberGUI"], "edit");
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
			case self::STATUS_NOT_ATTEMPTED:
				return $this->not_attemped;
			case self::STATUS_IN_PROGRESS:
				return $this->in_progress;
			case self::STATUS_SUCCESS:
				return $this->success;
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
	protected function getPassedCourse(\ilObjStudyProgramme $sp)
	{
		$progress = $sp->getProgressForAssignment($this->assignment_id);
		if (!$progress->isSuccessful() || $progress->isAccredited()) {
			return null;
		}
		// This should be a course as we are in the LP-Mode study programmes and
		// the node was not accredited.
		$crs_id = $progress->getCompletionBy();
		$crs_ref_id = gevObjectUtils::getRefId($crs_id);
		if ($crs_ref_id == null) {
			throw new \ilException("Cannot find ref_id for course '$crs_id'");
		}
		$crs_ref = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		if (!($crs_ref instanceof \ilObjCourseReference)) {
			throw new \ilException("Expected '$crs_ref_id' to be a course reference.");
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
	protected function getCourseWhereUserIsMember(\ilObjStudyProgramme $sp)
	{
		assert('$sp->getLPMode() == \ilStudyProgramme::MODE_LP_COMPLETED');
		foreach ($sp->getLPChildren() as $crs_ref) {
			$crs = ilObjectFactory::getInstanceByRefId($crs_ref->getTargetRefId(), false);
			if (!$crs) {
				continue;
			}
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
	protected function getManualAssessmentIn(\ilObjCourse $crs)
	{
		$sub_items = $crs->getSubItems();
		if (array_key_exists("mass", $sub_items)) {
			$item = array_shift($sub_items["mass"]);
			return ilObjectFactory::getInstanceByRefId($item['ref_id']);
		}
		return null;
	}

	/**
	 * Get the first Manual Assessment in the course where the user is a member.
	 *
	 * @parm	ilObjCourse $crs
	 * @return	ilObjManualAssessment|null
	 */
	protected function getManualAssessmentWhereUserIsMemberIn(\ilObjCourse $crs)
	{
		$sub_items = $crs->getSubItems();
		if (array_key_exists("mass", $sub_items)) {
			foreach ($sub_items['mass'] as $item) {
				$ia = ilObjectFactory::getInstanceByRefId($item['ref_id']);
				$members = $ia->loadMembers();
				if (!$members->userAllreadyMember($this->user)) {
					continue;
				}
				return $ia;
			}
		}
		return null;
	}

	protected function getResultInfo(\ilObjStudyProgramme $child)
	{
		$maybe_crs = $this->getPassedCourse($child);
		if ($maybe_crs === null) {
			return "-";
		}

		$ia = $this->getManualAssessmentWhereUserIsMemberIn($maybe_crs);
		if ($ia) {
			return $ia->membersStorage()->loadMember($ia, $this->user)->record();
		} else {
			return "-";
		}
	}
}
