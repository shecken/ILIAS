<?php

/**
 * Configuration of the deployment path takes place here.
 */

use CaT\IliasUserOrguImport as DUOI;

class ilFilesystemConfiguratorGUI
{

	const CMD_SHOW = 'show';
	const CMD_SAVE = 'save';

	const POST_DEPLOYMENT_PATH = 'deployment_path';

	/**
	 * @param	ilUserOrguImportPlugin	$plugin
	 * @param	ilUserOrguImportConfigGUI	$parent
	 */
	public function __construct($plugin, $parent)
	{
		$this->plugin = $plugin;
		$this->parent = $parent;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];

		$errors = new DUOI\ErrorReporting\ErrorCollection();

		$this->f = $this->plugin->getFactory($errors);
	}

	/**
	 * Execute current ctrl commad for this GUI
	 *
	 * @return void
	 */
	public function executeCommand()
	{
		$this->cmd = $this->ctrl->getCmd(self::CMD_SHOW);
		switch ($this->cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_SAVE:
				$this->save();
				break;
			default:
				$this->show();
		}
		return true;
	}

	protected function show()
	{
		$form = $this->getForm($this->f->FileSystemConfig());
		$this->tpl->setContent($form->getHTML());
	}

	protected function save()
	{
		$config = $this->f->FileSystemConfig();
		$form = $this->getForm($config);
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$path = $form->getItemByPostVar(self::POST_DEPLOYMENT_PATH)->getValue();
			if (is_readable($path) && is_dir($path)) {
				$config->withDeploymentPath($path);
				ilUtil::sendSuccess($this->plugin->txt('saved'));
				$this->show();
				return;
			} else {
				ilUtil::sendFailure($this->plugin->txt('invalid_path'));
			}
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function getForm($config)
	{
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt('fs_configuration'));

		$form->setFormAction($this->ctrl->getFormAction($this));

		$deployment_path = $config->deploymentPath();
		$em = new ilTextInputGUI($this->plugin->txt('deployment_path'), self::POST_DEPLOYMENT_PATH);
		$em->setRequired(true);
		if ($deployment_path !== null) {
			$em->setValue($deployment_path);
		}
		$form->addItem($em);

		$form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save'));
		return $form;
	}
}
