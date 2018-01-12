<?php

use CaT\IliasUserOrguImport as DUOI;

class ilExitUserConfiguratorGUI
{

	const CMD_SHOW = 'show';
	const CMD_SAVE = 'save';

	const POST_EXIT_GLOBAL_ROLE_ID = 'post_exit_global_role';

	public function __construct($plugin, $parent_gui)
	{
		$this->plugin = $plugin;
		$this->parent = $parent_gui;

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
		$form = $this->getForm($this->f->ExitConfig());
		$this->tpl->setContent($form->getHTML());
	}

	protected function save()
	{
		$form = $this->getForm();
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$role_id = (int)$form->getItemByPostVar(self::POST_EXIT_GLOBAL_ROLE_ID)->getValue();
			if (in_array($role_id, $this->f->IliasGlobalRoleManagement()->getGlobalRoles())) {
				$this->f->ExitConfig()->setExitRoleId($role_id);
				ilUtil::sendSuccess($this->plugin->txt('exit_role_saved'));
				$this->show();
				return;
			}
			ilUtil::sendFailure($this->plugin->txt('invalid_role'));
		} else {
			ilUtil::sendFailure($this->plugin->txt('invalid_input'));
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function getForm(DUOI\ExitConfig $config = null)
	{
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt('fs_configuration'));
		$form->setFormAction($this->ctrl->getFormAction($this));
		$global_roles_ids = $this->f->IliasGlobalRoleManagement()->getGlobalRoles();
		$global_roles = [];
		foreach ($global_roles_ids as $role_id) {
			$global_roles[$role_id] = \ilObject::_lookupTitle($role_id);
		}
		asort($global_roles);

		$exit_global = new ilSelectInputGUI($this->plugin->txt('exit_global_role'), self::POST_EXIT_GLOBAL_ROLE_ID);
		$exit_global->setRequired(true);
		$exit_global->setOptions($global_roles);

		if ($config) {
			$exit_role = $config->exitRoleId();

			if ($exit_role !== 0) {
				$exit_global->setValue($exit_role);
			}
		}
		$form->addItem($exit_global);
		$form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save'));
		return $form;
	}
}
