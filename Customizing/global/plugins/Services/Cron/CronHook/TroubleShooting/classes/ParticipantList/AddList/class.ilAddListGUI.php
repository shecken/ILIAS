<?php

require_once __DIR__."/../../PluginLanguage.php";

class ilAddListGUI {
	use PluginLanguage;

	const CMD_SHOW_FORM = "showForm";
	const CMD_ADD_LIST = "addList";
	const CRS_ID = "crs_id";
	const FILE = "file";

	public function __construct(
		ilParticipantListGUI $parent,
		ilCtrl $ctrl,
		ilTemplate $tpl,
		Closure $txt,
		ilAddListActions $actions
	) {
		$this->parent = $parent;
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->txt = $txt;
		$this->actions = $actions;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case self::CMD_SHOW_FORM:
				$this->showForm();
				break;
			case self::CMD_ADD_LIST:
				$this->addList();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	protected function showForm(ilPropertyFormGUI $form = null)
	{
		if(is_null($form)) {
			$form = $this->initForm();
		}

		$this->tpl->setContent($form->getHtml());
	}

	protected function addList()
	{
		$form = $this->initForm();
		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$post = $_POST;
		$crs_ref_id = $post[self::CRS_ID];
		$crs = ilObject::getInstanceByRefId($crs_ref_id);
		$file = $post[self::FILE];

		if(!$this->actions->isCourseFinalized($crs->getId())) {
			ilUtil::sendInfo($this->txt("add_list_course_not_finalized"), true);
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		if(!$_FILES[self::FILE]["tmp_name"])
		{
			ilUtil::sendFailure($this->txt("add_list_no_list_uploaded"), true);
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$participation_status = ilParticipationStatus::getInstance($crs);
		if(!$participation_status->uploadAttendanceList($_FILES[self::FILE]))
		{
			ilUtil::sendSuccess($lng->txt("add_list_list_not_uploaded"), true);
		} else {
			ilUtil::sendSuccess($lng->txt("add_list_list_uploaded"), true);
		}

		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}

	protected function initForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->txt("add_list_form_title"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$ti = new ilNumberInputGUI($this->txt("add_list_crs_id"), self::CRS_ID);
		$form->addItem($ti);

		$fi = new ilFileInputGUI($this->txt("add_list_participant_list_file"), self::FILE);
		$form->addItem($fi);

		$form->addCommandButton(self::CMD_ADD_LIST, $this->txt("add_list_add_list"));
		$form->addCommandButton(self::CMD_SHOW_FORM, $this->txt("cancel"));

		return $form;
	}
}