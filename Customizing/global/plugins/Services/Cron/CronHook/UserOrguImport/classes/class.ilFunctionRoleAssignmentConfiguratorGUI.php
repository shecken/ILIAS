<?php

use CaT\IliasUserOrguImport as UOI;

class ilFunctionRoleAssignmentConfiguratorGUI
{
	const CMD_SHOW = 'show';
	const CMD_SAVE = 'save';
	const POST_SUPERIOR_FUNCTIONS = 'superior_functions';
	const POST_EMPLOYEE_FUNCTIONS = 'employee_functions';

	const POST_EMPLOYEE_GLOBAL_ROLE_ID = 'employee_global_role_id';
	const POST_SUPERIOR_GLOBAL_ROLE_ID = 'superior_global_role_id';

	public function __construct($plugin, $parent)
	{
		$this->plugin = $plugin;
		$this->parent = $parent;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];

		$errors = new UOI\ErrorReporting\ErrorCollection();

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
				$this->showAssignments();
				break;
			case self::CMD_SAVE:
				$this->saveAssignments();
				break;
			default:
				$this->showAssignments();
		}
		return true;
	}

	protected function showAssignments()
	{
		$cfg = $this->f->UserOrguAssignmentsFactory()->UserOrguFunctionConfigDB()->load();
		$form = $this->initForm();

		$form->setValuesByArray(
			[self::POST_SUPERIOR_FUNCTIONS => $cfg->superiorFunctions()
			,self::POST_EMPLOYEE_FUNCTIONS => $cfg->employeeFunctions()
			,self::POST_SUPERIOR_GLOBAL_ROLE_ID => $cfg->superiorGlobalRoleId()
			,
			self::POST_EMPLOYEE_GLOBAL_ROLE_ID => $cfg->employeeGlobalRoleId()]
		);

		$this->tpl->setContent($form->getHTML());
	}

	protected function initForm()
	{
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($title);
		$form->setFormAction($this->ctrl->getFormAction($this));

		$superior_functions = new ilTextInputGUI($this->plugin->txt('superior_functions'), self::POST_SUPERIOR_FUNCTIONS);
		$superior_functions->setMulti(true);
		$superior_functions->setRequired(true);
		$form->addItem($superior_functions);

		$employee_functions = new ilTextInputGUI($this->plugin->txt('employee_functions'), self::POST_EMPLOYEE_FUNCTIONS);
		$employee_functions->setMulti(true);
		$employee_functions->setRequired(true);
		$form->addItem($employee_functions);

		$global_roles_ids = $this->f->IliasGlobalRoleManagement()->getGlobalRoles();
		$global_roles = [];
		foreach ($global_roles_ids as $role_id) {
			$global_roles[$role_id] = \ilObject::_lookupTitle($role_id);
		}

		asort($global_roles);

		$superior_global = new ilSelectInputGUI($this->plugin->txt('superior_global_role'), self::POST_SUPERIOR_GLOBAL_ROLE_ID);
		$superior_global->setRequired(true);
		$superior_global->setOptions($global_roles);
		$form->addItem($superior_global);

		$employee_global = new ilSelectInputGUI($this->plugin->txt('employee_global_role'), self::POST_EMPLOYEE_GLOBAL_ROLE_ID);
		$employee_global->setRequired(true);
		$employee_global->setOptions($global_roles);
		$form->addItem($employee_global);

		$form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save'));
		return $form;
	}

	protected function saveAssignments()
	{
		$form = $this->initForm();
		$form->setValuesByPost();
		if ($form->checkInput()) {
			try {
				$config = UOI\UserOrguAssignments\UserOrguFunctionConfig::getInstanceByArrays(
					$this->postprocessInput($form->getInput(self::POST_SUPERIOR_FUNCTIONS)),
					$this->postprocessInput($form->getInput(self::POST_EMPLOYEE_FUNCTIONS))
				);
				$superior_role_id = (int)$form->getInput(self::POST_SUPERIOR_GLOBAL_ROLE_ID);
				$employee_role_id = (int)$form->getInput(self::POST_EMPLOYEE_GLOBAL_ROLE_ID);
				$global_roles = $this->f->IliasGlobalRoleManagement()->getGlobalRoles();
				if (in_array($superior_role_id, $global_roles) && in_array($employee_role_id, $global_roles)) {
					$config = $config->withSuperiorGlobalRoleId($superior_role_id)->withEmployeeGlobalRoleId($employee_role_id);
					$this->f->UserOrguAssignmentsFactory()->UserOrguFunctionConfigDB()->save($config);
					ilUtil::sendSuccess($this->plugin->txt('functions_saved'));
				} else {
					ilUtil::sendFailure($this->plugin->txt('provided_role_ids_are_invalid'));
				}
				$this->showAssignments();
			} catch (UOI\UserOrguAssignments\Exception $e) {
				ilUtil::sendFailure($this->plugin->txt('function_role_overlap_error'));
				$this->tpl->setContent($form->getHTML());
			}
		}
	}

	private function postprocessInput(array $values)
	{
		$return = [];
		foreach ($values as $value) {
			if (trim((string)$value) !== '') {
				$return[] = $value;
			}
		}
		return array_unique($return);
	}
}
