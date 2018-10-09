<?php

class FileConfigurationGUI
{
	const CMD_SHOW_CONFIGURATION = "show";
	const CMD_SAVE = "save";

	const F_FILE_PATH = "file_path";

	public function __construct(
		ilCtrl $ctrl,
		ilTemplate $tpl,
		ilDiMAkImportPlugin $plugin
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->plugin = $plugin;
		$this->actions = $plugin->getFileActions();
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case self::CMD_SHOW_CONFIGURATION:
				$this->show();
				break;
			case self::CMD_SAVE:
				$this->save();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	protected function show(ilPropertyFormGUI $form = null)
	{
		if(is_null($form)) {
			$form = $this->initForm();
			$this->fillForm($form);
		}

		$this->tpl->setContent($form->getHtml());
	}

	protected function save()
	{
		$form = $this->initForm();
		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$post = $_POST;
		$path = $post[self::F_FILE_PATH];
		if (!is_readable($path) || !is_dir($path)) {
			ilUtil::sendFailure(sprintf($this->txt("path_not_accessed"), $path));
			$form->setValuesByPost();
			$this->show($form);
			return;
		}
		$this->actions->save($path);
		$this->ctrl->redirect($this, self::CMD_SHOW_CONFIGURATION);
	}

	protected function initForm()
	{
		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt("fs_configuration"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$em = new ilTextInputGUI($this->txt("file_path"), self::F_FILE_PATH);
		$em->setRequired(true);
		$form->addItem($em);

		$form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
		$form->addCommandButton(self::CMD_SHOW_CONFIGURATION, $this->txt("cancel"));
		return $form;
	}

	protected function fillForm(ilPropertyFormGUI $form)
	{
		$values  = [];
		$file_config = $this->actions->read();
		$values[self::F_FILE_PATH] = $file_config->getPath();
		$form->setValuesByArray($values);
	}

	protected function txt($code)
	{
		return $this->plugin->txt($code);
	}
}