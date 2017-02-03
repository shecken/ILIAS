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
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd("view");
		$next_class = $this->g_ctrl->getNextClass();

		switch ($next_class) {
			default:
				switch ($cmd) {
					case "view":
						break;
					default:
						throw new Exception("command unkown: $cmd");
				}
		}

		$this->$cmd();
	}

	protected function view()
	{
		require_once("Services/GEV/VAPass/classes/class.gevMyVAPassTableGUI.php");
		$tbl = new gevMyVAPassTableGUI($this, $this->getNodeRefId(), $this->getUserId(), $this->getAssignmentId(), "view");

		$this->g_tpl->setContent($tbl->getHtml());
	}

	protected function getNodeRefId()
	{
		$get = $_GET;

		if ($get["nodeRefId"] && $get["nodeRefId"] !== null && is_integer((int)$post["nodeRefId"])) {
			return (int)$_GET["nodeRefId"];
		}
		return 2167;
		throw new Exception("No studyprogramme node id given");
	}

	protected function getUserId()
	{
		$get = $_GET;

		if ($get["user_id"] && $get["user_id"] !== null && is_integer((int)$post["user_id"])) {
			return (int)$_GET["user_id"];
		}
		global $ilUser;
		return $ilUser->getId();
		throw new Exception("No user id given");
	}

	protected function getAssignmentId()
	{
		$get = $_GET;

		if ($get["assignment_id"] && $get["assignment_id"] !== null && is_integer((int)$post["assignment_id"])) {
			return (int)$_GET["assignment_id"];
		}
		return 128;
		throw new Exception("No assignment id given");
	}
}
