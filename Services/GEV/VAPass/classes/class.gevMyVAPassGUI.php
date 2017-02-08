<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This GUI will show the member of an VA Pass the historie of his own learning progress
 * and which study programme and course he has to do
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class gevMyVAPassGUI
{

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	public function __construct()
	{
		global $ilCtrl, $tpl;

		$this->g_ctrl = $ilCtrl;
		$this->g_tpl = $tpl;

		$this->success = '<img src="'.ilUtil::getImagePath("gev_va_pass_success_icon.png").'" />';
		$this->in_progress = '<img src="'.ilUtil::getImagePath("gev_va_pass_progress_icon.png").'" />';
		$this->faild = '<img src="'.ilUtil::getImagePath("gev_va_pass_failed_icon.png").'" />';
		$this->not_attemped = '<img src="'.ilUtil::getImagePath("gev_va_pass_not_attemped_icon.png").'" />';
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd("view");
		$next_class = $this->g_ctrl->getNextClass();

		switch ($next_class) {
			default:
				switch ($cmd) {
					case "view":
					case "showContent":
						break;
					default:
						throw new Exception("command unkown: $cmd");
				}
		}

		$this->$cmd();
	}

	protected function view()
	{
		//WERTE FÜR USER_ID, SP_REF_ID und ASSIGNMENT_ID ermitterln

		$this->showContent();
	}

	protected function showContent()
	{
		$relevant_children = $this->getRelevantChildren();
		$with_children = $this->getSPWithChildrenBelow($relevant_children);
		$with_lp_children = $this->getSPWithLPChildren($relevant_children);

		$html = "";
		if (count($with_children) > 0) {
			require_once("Services/GEV/VAPass/classes/class.gevMyVAPassTableGUI.php");
			$tbl_children = new gevMyVAPassTableGUI($this, $with_children, $this->getAssignmentId(), $this->getUserId(), "view");
			$tbl_children->setTitle($this->getStudyProgramme()->getTitle());
			$tbl_children->setSubtitle($this->getStudyProgramme()->getDescription());
			$tbl_children->setLegend($this->createLegend());

			$html = $tbl_children->getHtml();
		}

		if (count($with_lp_children) > 0) {
			require_once("Services/GEV/VAPass/classes/class.gevMyVAPassCourseTableGUI.php");
			$tbl_lp_children = new gevMyVAPassCourseTableGUI($this, $with_lp_children, $this->getAssignmentId(), $this->getUserId(), "view");

			if ($html == "") {
				$tbl_lp_children->setTitle($this->getStudyProgramme()->getTitle());
				$tbl_lp_children->setSubtitle($this->getStudyProgramme()->getDescription());
				$tbl_lp_children->setLegend($this->createLegend());
				$html = $tbl_lp_children->getHtml();
			} else {
				$html .= "<br />".$tbl_lp_children->getHtml();
			}
		}

		$this->g_tpl->setContent($html);
	}

	protected function getStudyProgramme()
	{
		$sp_ref_id = $this->getSPRefId();

		if (!array_key_exists($sp_ref_id, $this->studyprogramme)) {
			require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
			$this->studyprogramme[$sp_ref_id] = new ilObjStudyProgramme($sp_ref_id);
		}

		return $this->studyprogramme[$sp_ref_id];
	}

	protected function getRelevantChildren($children)
	{
		$sp = $this->getStudyProgramme();
		$ret = array();

		foreach ($sp->getChildren() as $child) {
			if ($this->isRelevant($this->getAssignmentId(), $child->getId(), $this->getUserId())) {
				$ret[] = $child;
			}
		}

		return $ret;
	}

	protected function getSPWithChildrenBelow($children)
	{
		return array_filter($children, function ($child) {
			if ($child->hasChildren()) {
				return $child;
			}
		});
	}

	protected function getSPWithLPChildren($children)
	{
		return array_filter($children, function ($child) {
			if ($child->hasLPChildren()) {
				return $child;
			}
		});
	}

	/**
	 * Creates the legend for title
	 *
	 * @return catLegendGUI
	 */
	public function createLegend()
	{
		$legend = new catLegendGUI();
		$legend->addItem($this->success, "gev_va_pass_success")
			   ->addItem($this->in_progress, "gev_va_pass_progress")
			   ->addItem($this->faild, "gev_va_pass_failed")
			   ->addItem($this->not_attemped, "gev_va_pass_not_attemped");

		return $legend;
	}

	public function getStatusIcon($status)
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

	public function isRelevant($ass_id, $sp_id, $user_id)
	{
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
		$progress = ilStudyProgrammeUserProgress::getInstance($ass_id, $sp_id, $user_id);

		return $progress->isRelevant();
	}

	protected function getSPRefId()
	{
		if ($this->sp_ref_id === null) {
			throw new Exception("No studyprogramme node id given");
		}

		return $this->sp_ref_id;
	}

	protected function getUserId()
	{
		if ($this->user_id === null) {
			throw new Exception("No user id given");
		}

		return $this->user_id;
	}

	protected function getAssignmentId()
	{
		if ($this->assignment_id === null) {
			throw new Exception("No assignment id given");
		}

		return $this->assignment_id;
	}

	protected function findSPRefId()
	{
		$get = $_GET;

		if ($get["spRefId"] && $get["spRefId"] !== null && is_integer((int)$post["spRefId"])) {
			$this->sp_ref_id = (int)$_GET["spRefId"];
		}
	}

	protected function findUserId()
	{
		$get = $_GET;

		if ($get["user_id"] && $get["user_id"] !== null && is_integer((int)$post["user_id"])) {
			$this->user_id = (int)$_GET["user_id"];
		}
	}

	protected function findAssignmentId()
	{
		$get = $_GET;

		if ($get["assignment_id"] && $get["assignment_id"] !== null && is_integer((int)$post["assignment_id"])) {
			$this->assignment_id =  (int)$_GET["assignment_id"];
		}
	}

	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	public function setAssignmentId($assignment_id)
	{
		$this->assignment_id = $assignment_id;
	}

	public function setSPRefId($sp_ref_id)
	{
		$this->sp_ref_id = $sp_ref_id;
	}
}
