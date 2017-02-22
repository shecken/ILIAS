<?php

use \CaT\Plugins\ReportStudyProgramme;

class ilReportStudyProgrammeSettingsGUI
{
	use ReportStudyProgramme\Settings\ilFormHelper;

	const EDIT_SETTINGS = "editProperties";
	const SAVE_SETTINGS = "saveSettings";
	public function __construct(ReportStudyProgramme\ilActions $actions, Closure $txt)
	{
		global $ilCtrl, $tpl;

		$this->g_ctrl = $ilCtrl;
		$this->g_tpl = $tpl;
		$this->actions = $actions;
		$this->txt = $txt;
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd(self::EDIT_SETTINGS);

		switch ($cmd) {
			case self::EDIT_SETTINGS:
			case self::SAVE_SETTINGS:
				$this->$cmd();
				break;
			default:
				throw new Exception("ilReportStudyProgrammeSettingsGUI::executeCommand: cmd not found: $cmd");
		}
	}

	protected function editProperties($form = null)
	{
		if ($form === null) {
			$form = $this->initForm();
			$form = $this->fillForm($form);
		}

		$form->addCommandButton(self::SAVE_SETTINGS, $this->txt("save"));
		$form->addCommandButton(self::EDIT_SETTINGS, $this->txt("cancel"));

		$this->g_tpl->setContent($form->getHtml());
	}

	protected function saveSettings()
	{
		$form = $this->initForm();

		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->editProperties($form);
			return;
		}

		$post = $_POST;
		$this->actions->updateObjectFromArray($post);

		$this->g_ctrl->redirect($this);
	}

	protected function initForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormAction($this));

		$ti = new ilTextInputGUI($this->txt("settings_title"), ReportStudyProgramme\ilActions::F_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$ta = new ilTextareaInputGUI($this->txt("setting_description"), ReportStudyProgramme\ilActions::F_DESCRIPTION);
		$form->addItem($ta);

		$this->addSettingsEditFormItems($form);

		return $form;
	}

	protected function fillForm($form)
	{
		$current = $this->actions->getObject();
		$settings = $current->getSettings();

		$values = array(ReportStudyProgramme\ilActions::F_TITLE => $current->getTitle()
			, ReportStudyProgramme\ilActions::F_DESCRIPTION => $current->getDescription()
			);

		$this->getSettingValues($settings, $values);
		$form->setValuesByArray($values);

		return $form;
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code)
	{
		assert('is_string($code)');
		$txt = $this->txt;

		return $txt($code);
	}
}
