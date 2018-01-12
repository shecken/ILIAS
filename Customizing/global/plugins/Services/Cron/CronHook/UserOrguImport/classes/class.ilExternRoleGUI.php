<?php

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

use CaT\IliasUserOrguImport as DUOI;

class ilExternRoleGUI
{

	const CMD_SAVE = 'save';
	const CMD_EDIT = 'edit';
	const CMD_SAVE_NEW = 'save_new';
	const CMD_CREATE_REQUEST = 'create_request';
	const CMD_BACK = 'back';

	const POST_EXTERN_ROLE = 'extern_role';
	const POST_EXTERN_ROLE_ID = 'extern_role_id';
	const POST_EXTERN_ROLE_DESC = 'extern_role_desc';
	const POST_ROLES = 'roles';

	const GET_EXTERN_ROLE_ID = 'extern_role_id';

	protected $plugin;
	protected $parent;

	protected $rc;

	protected $ext_role_id;

	public function __construct($plugin, $parent_gui)
	{
		$this->plugin = $plugin;
		$this->parent = $parent_gui;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];


		$errors = new DUOI\ErrorReporting\ErrorCollection();

		$this->actions = new DUOI\User\ilUserActions($this->plugin->getFactory($errors)->UserFactory());

		$this->rc = $this->plugin->getFactory($errors)->UserFactory()->RoleConfiguration();
	}

	public function executeCommand()
	{
		$this->cmd = $this->ctrl->getCmd(self::CMD_EDIT);
		switch ($this->cmd) {
			case self::CMD_CREATE_REQUEST:
				$this->createRequest();
				break;
			case self::CMD_SAVE:
				$this->save();
				break;
			case self::CMD_EDIT:
				$this->edit();
				break;
			case self::CMD_SAVE_NEW:
				$this->saveNew();
				break;
			case self::CMD_BACK:
				$this->back();
		}
		return true;
	}

	protected function edit()
	{
		$this->ext_role_id = $_GET[self::GET_EXTERN_ROLE_ID];

		$this->showEdit();
	}

	protected function saveNew()
	{
		$form = $this->form();
		$form->setValuesByPost();
		$check_input = $form->checkInput();
		$title = $form->getItemByPostVar(self::POST_EXTERN_ROLE)->getValue();
		$check_ext_role_exists = $this->rc->externRoleExists($title);
		$roles = $this->clearRoles($form->getInput(self::POST_ROLES));

		if (!$check_input) {
			$this->createRequest();
		} elseif ($check_ext_role_exists) {
			\ilUtil::sendFailure($this->plugin->txt('role_exists_failure'));
			$this->createRequest();
		} elseif (count($roles) === 0) {
			\ilUtil::sendFailure($this->plugin->txt('no_roles_failure'));
			$this->createRequest();
		} else {
			$desc = $form->getItemByPostVar(self::POST_EXTERN_ROLE_DESC)->getValue();

			$this->rc = $this->actions->addExternRole($title, $desc, $roles);
			$this->ext_role_id = (int)$this->rc->externRoleIdForExternRole($title);
			\ilUtil::sendSuccess($this->plugin->txt('new_role_created'), true);
			$this->back();
		}
	}

	protected function save()
	{
		$form = $this->editForm();

		$form->setValuesByPost();
		$check_input = $form->checkInput();
		$title = $form->getItemByPostVar(self::POST_EXTERN_ROLE)->getValue();
		$this->ext_role_id = (int)$form->getItemByPostVar(self::POST_EXTERN_ROLE_ID)->getValue();
		$invalid_name = $this->rc->externRoleExists($title)
					&& $this->rc->externRoleIdForExternRole($title) !== $this->ext_role_id;
		$roles = $this->clearRoles($form->getInput(self::POST_ROLES));
		if ($check_input) {
			if ($invalid_name) {
				\ilUtil::sendFailure($this->plugin->txt('role_exists_failure'));
			} elseif (count($roles) === 0) {
				\ilUtil::sendFailure($this->plugin->txt('no_roles_failure'));
			} else {
				$desc = $form->getItemByPostVar(self::POST_EXTERN_ROLE_DESC)->getValue();
				$this->rc = $this->actions->updateExternRole($this->ext_role_id, $title, $desc, $roles);
				\ilUtil::sendSuccess($this->plugin->txt('role_updated'));
			}
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function clearRoles(array $roles)
	{
		$filtered = [];
		foreach ($roles as $role) {
			$role = (int)$role;
			if ($role > 0) {
				$filtered[] = $role;
			}
		}
		return array_unique($filtered);
	}

	protected function editForm()
	{
		$form = $this->form($this->plugin->txt('edit_extern'));

		$form->addCommandButton(self::CMD_BACK, $this->plugin->txt('back'));
		$form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save'));

		return $form;
	}

	protected function showEdit()
	{
		$form = $this->editForm();
		$form->setValuesByArray($this->valuesForForm($this->rc));
		$this->tpl->setContent($form->getHTML());
	}

	protected function valuesForForm(DUOI\User\RoleConfiguration $rc)
	{
		$extern_role = $rc->externRoleForExternRoleId((int)$this->ext_role_id);
		return [
			self::POST_ROLES => $rc->roleIdsFor($extern_role),
			self::POST_EXTERN_ROLE => $extern_role,
			self::POST_EXTERN_ROLE_DESC => $rc->externRoleDescription($extern_role),
			self::POST_EXTERN_ROLE_ID => (int)$this->ext_role_id
		];
	}

	protected function createRequest()
	{
		$form = $this->form($this->plugin->txt('create_extern'));

		$form->addCommandButton(self::CMD_BACK, $this->plugin->txt('abort'));
		$form->addCommandButton(self::CMD_SAVE_NEW, $this->plugin->txt('create'));

		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	protected function back()
	{
		$this->ctrl->redirectByClass(
			['ilObjComponentSettingsGUI', 'ilUserOrguImportConfigGUI', 'ilexternrolesconfiguratorgui'],
			ilExternRolesConfiguratorGUI::CMD_SHOW
		);
	}

	protected function form($title = "")
	{
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($title);

		$form->setFormAction($this->ctrl->getFormAction($this));
		$title = new ilTextInputGUI(
			$this->plugin->txt('extern_role_title'),
			self::POST_EXTERN_ROLE
		);
		$title->setRequired(true);
		$title->setMaxLength(32);
		$form->addItem($title);

		$desc = new ilTextAreaInputGUI(
			$this->plugin->txt('extern_role_description'),
			self::POST_EXTERN_ROLE_DESC
		);
		$form->addItem($desc);

		$roles = new ilSelectInputGUI($this->plugin->txt('ilias_roles'), self::POST_ROLES);
		$roles->setMulti(true);
		$roles->setRequired(true);
		$global_roles = [ -1 => '--'];
		foreach ($this->rc->globalRoleIds() as $role_id) {
			$global_roles[$role_id] = \ilObject::_lookupTitle($role_id);
		}
		asort($global_roles);

		$roles->setOptions($global_roles);
		$form->addItem($roles);

		$ext_role_id = new ilHiddenInputGUI(self::POST_EXTERN_ROLE_ID);

		$form->addItem($ext_role_id);
		return $form;
	}
}
