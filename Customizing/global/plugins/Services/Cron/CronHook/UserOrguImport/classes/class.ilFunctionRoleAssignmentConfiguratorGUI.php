<?php

use CaT\IliasUserOrguImport as UOI;

class ilFunctionRoleAssignmentConfiguratorGUI
{
	const CMD_SHOW = 'show';
	const CMD_SAVE = 'save';
	const POST_SUPERIOR_FUNCTIONS = 'superior_functions';
	const POST_EMPLOYEE_FUNCTIONS = 'employee_functions';
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
		$form->setValuesByArray([self::POST_SUPERIOR_FUNCTIONS => $cfg->superiorFunctions(),self::POST_EMPLOYEE_FUNCTIONS => $cfg->employeeFunctions()]);
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

		$form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save'));
		return $form;
	}

	protected function saveAssignments()
	{
		$form = $this->initForm();
		$form->setValuesByPost();
		if($form->checkInput()) {
			try {
				$this->f->UserOrguAssignmentsFactory()->UserOrguFunctionConfigDB()->save(
					UOI\UserOrguAssignments\UserOrguFunctionConfig::getInstanceByArrays(
						$this->postprocessInput($form->getInput(self::POST_SUPERIOR_FUNCTIONS))
						,$this->postprocessInput($form->getInput(self::POST_EMPLOYEE_FUNCTIONS))
					)
				);
				$this->showAssignments();
			} catch(UOI\UserOrguAssignments\Exception $e) {
				ilUtil::sendFailure($this->plugin->txt('function_role_overlap_error'));
				$this->tpl->setContent($form->getHTML());
			}
		}
	}

	private function postprocessInput(array $values)
	{
		$return = [];
		foreach($values as $value) {
			if(trim((string)$value) !== '') {
				$return[] = $value;
			}
		}
		return array_unique($return);
	}

}