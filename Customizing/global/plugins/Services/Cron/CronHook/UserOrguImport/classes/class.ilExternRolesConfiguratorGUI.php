<?php

use CaT\IliasUserOrguImport as DUOI;

require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilExternRoleGUI.php';
require_once './Services/Utilities/classes/class.ilConfirmationGUI.php';

/**
 * @ilCtrl_Calls ilExternRolesConfiguratorGUI: ilExternRoleGUI
 */
class ilExternRolesConfiguratorGUI
{

	const CMD_REMOVE_EXTERN_ROLES = 'remove_extern_roles';
	const CMD_REMOVE_EXTERN_ROLE = 'remove_extern_role';
	const CMD_REQUEST_REMOVE_EXTERN_ROLES = 'request_remove_extern_roles';
	const CMD_REQUEST_REMOVE_EXTERN_ROLE = 'request_remove_extern_role';
	const CMD_SHOW = 'show';

	const GET_EXTERN_ROLE_ID = 'get_extern_role_id';
	const POST_EXTERN_ROLE_ID = 'post_extern_role_id';
	const POST_EXTERN_ROLE_IDS = 'post_extern_role_ids';

	protected $plugin;
	protected $parent;

	protected $tpl;

	public function __construct($plugin, $parent)
	{
		$this->plugin = $plugin;
		$this->parent = $parent;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];

		$errors = new DUOI\ErrorReporting\ErrorCollection();
		$this->f = $this->plugin->getFactory($errors);
		$this->actions = new DUOI\User\ilUserActions($this->f->UserFactory());
	}

	public function executeCommand()
	{
		$this->cmd = $this->ctrl->getCmd(self::CMD_SHOW);
		switch ($this->cmd) {
			case self::CMD_REQUEST_REMOVE_EXTERN_ROLES:
				$this->requestRemoveExternRoles();
				break;
			case self::CMD_REQUEST_REMOVE_EXTERN_ROLE:
				$this->requestRemoveExternRole();
				break;
			case self::CMD_REMOVE_EXTERN_ROLES:
				$this->removeExternRoles();
				break;
			case self::CMD_REMOVE_EXTERN_ROLE:
				$this->removeExternRole();
				break;
			case self::CMD_SHOW:
			case 'configure':
				$this->show();
				break;
			default:
				$next_class = $this->ctrl->getNextClass();
				switch ($next_class) {
					case 'ilexternrolegui':
						$this->externRole();
						break;
					default:
						$this->show();
				}
		}
		return true;
	}

	protected function requestRemoveExternRoles()
	{
		$confirm = new ilConfirmationGUI();
		$extern_role_ids = $_POST['selected'];
		if (is_array($extern_role_ids)) {
			foreach ($extern_role_ids as $extern_role_id) {
				$extern_roles[] = $this->externRolesConfig()->externRoleForExternRoleId((int)$extern_role_id);
				$confirm->addHiddenItem(self::POST_EXTERN_ROLE_IDS.'[]', $extern_role_id);
			}
			$confirm->setHeaderText(sprintf($this->plugin->txt('delete_ext_roles_conf_head'), implode(', ', $extern_roles)));
			$confirm->setFormAction($this->ctrl->getFormAction($this));
			$confirm->setConfirm($this->plugin->txt('delete'), self::CMD_REMOVE_EXTERN_ROLES);
			$confirm->setCancel($this->plugin->txt('cancel'), self::CMD_SHOW);
			$this->tpl->setContent($confirm->getHTML());
		} else {
			\ilUtil::sendInfo($this->plugin->txt('no_role_chosen'));
			$this->show();
		}
	}

	protected function requestRemoveExternRole()
	{
		$extern_role_id = (int)$_GET[self::GET_EXTERN_ROLE_ID];
		$extern_role = $this->externRolesConfig()->externRoleForExternRoleId($extern_role_id);
		$confirm = new ilConfirmationGUI();
		$confirm->addHiddenItem(self::POST_EXTERN_ROLE_ID, $extern_role_id);
		$confirm->setHeaderText(sprintf($this->plugin->txt('delete_ext_role_conf_head'), $extern_role));
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->plugin->txt('delete'), self::CMD_REMOVE_EXTERN_ROLE);
		$confirm->setCancel($this->plugin->txt('cancel'), self::CMD_SHOW);
		$this->tpl->setContent($confirm->getHTML());
	}

	protected function removeExternRoles()
	{
		$selected = $_POST[self::POST_EXTERN_ROLE_IDS];

		if (is_array($selected)) {
			foreach ($selected as $extern_role_id) {
				$this->actions->deleteExternRole((int)$extern_role_id);
			}
		}
		\ilUtil::sendInfo($this->plugin->txt('ext_roles_deleted'));
		$this->show();
	}

	protected function removeExternRole()
	{

		$extern_role_id = $_POST[self::POST_EXTERN_ROLE_ID];
		$this->actions->deleteExternRole((int)$extern_role_id);
		\ilUtil::sendInfo($this->plugin->txt('ext_role_deleted'));
		$this->show();
	}

	protected function externRolesConfig()
	{
		return $this->f->UserFactory()->RoleConfiguration();
	}

	protected function show()
	{

		$this->tpl->setContent($this->creationLinkButton()->render(). $this->extRolesTable()->getHTML());
	}

	protected function creationLinkButton()
	{
		$lb =  ilLinkButton::getInstance();
		$lb->setUrl($this->ctrl->getLinkTargetByClass('ilExternRoleGUI', ilExternRoleGUI::CMD_CREATE_REQUEST));
		$lb->setCaption($this->plugin->txt('create'), false);
		return $lb;
	}

	protected function extRolesTable()
	{
		return new DUOI\ExternRoleTableGUI($this->externRolesConfig(), $this->plugin, $this, $this->ctrl->getCmd(self::CMD_SHOW));
	}


	protected function externRole()
	{
		$gui = new ilExternRoleGUI($this->plugin, $this);
		$this->ctrl->forwardCommand($gui);
	}
}
