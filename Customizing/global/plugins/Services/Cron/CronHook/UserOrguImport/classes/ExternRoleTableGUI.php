<?php

namespace CaT\IliasUserOrguImport;

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

class ExternRoleTableGUI extends \ilTable2GUI
{
	public function __construct(
		User\RoleConfiguration $a_config,
		$a_plugin,
		$a_parent_obj,
		$a_parent_cmd
	) {

		global $DIC;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->plugin = $a_plugin;
		$this->parent = $a_parent_obj;

		$this->ctrl = $DIC['ilCtrl'];

		$this->setEnableTitle(true);
		$this->setTitle($title);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(false);

		$this->setFormAction($DIC['ilCtrl']->getFormAction($this->parent));
		$this->addCommandButton(
			\ilExternRolesConfiguratorGUI::CMD_REQUEST_REMOVE_EXTERN_ROLES,
			$a_plugin->txt("delete_ext_role")
		);
		$this->setRowTemplate("tpl.extern_role_row.html", $a_plugin->getDirectory());

		$this->setData($this->createData($a_config));

		$this->addColumn('', '');
		$this->addColumn($a_plugin->txt('extern_role'));
		$this->addColumn($a_plugin->txt('role_ilias'));
		$this->addColumn($a_plugin->txt('extern_role_description'));
		$this->addColumn($a_plugin->txt('actions'));
	}

	protected function createData(User\RoleConfiguration $a_config)
	{
		$data = [];
		foreach ($a_config->externRoles() as $extern_role) {
			$aux = [];
			$aux['extern_role_id'] = $a_config->externRoleIdForExternRole($extern_role);
			$aux['extern_role'] = $extern_role;
			$aux['desc'] = $a_config->externRoleDescription($extern_role);
			$aux['roles'] = implode(',', array_map(function ($role_id) {
				return \ilObject::_lookupTitle($role_id);
			}, $a_config->roleIdsFor($extern_role)));
			$aux['actions'] = $this->actionMenuFor($aux['extern_role_id']);
			$data[] = $aux;
		}
		return $data;
	}

	protected function actionMenuFor($extern_role_id)
	{

		$l = new \ilAdvancedSelectionListGUI();
		$l->setListTitle($this->plugin->txt("please_choose"));

		$this->ctrl->setParameterByClass(
			'ilExternRoleGUI',
			\ilExternRoleGUI::GET_EXTERN_ROLE_ID,
			$extern_role_id
		);
		$this->ctrl->setParameterByClass(
			'ilExternRolesConfiguratorGUI',
			\ilExternRolesConfiguratorGUI::GET_EXTERN_ROLE_ID,
			$extern_role_id
		);

		$l->addItem(
			$this->plugin->txt('edit'),
			'edit',
			$this->ctrl->getLinkTargetByClass(
				'ilExternRoleGUI',
				\ilExternRoleGUI::CMD_EDIT
			)
		);
		$l->addItem(
			$this->plugin->txt('delete_ext_role'),
			'remove',
			$this->ctrl->getLinkTarget(
				$this->parent,
				\ilExternRolesConfiguratorGUI::CMD_REQUEST_REMOVE_EXTERN_ROLE
			)
		);

		$this->ctrl->setParameterByClass(
			'ilExternRoleGUI',
			\ilExternRoleGUI::GET_EXTERN_ROLE_ID,
			null
		);
		$this->ctrl->setParameterByClass(
			'ilExternRolesConfiguratorGUI',
			\ilExternRolesConfiguratorGUI::GET_EXTERN_ROLE_ID,
			null
		);
		$l->setId("selection_list_".$extern_role_id);
		return $l->getHTML();
	}


	protected function fillRow($set)
	{
		$this->tpl->setVariable('EXTERN_ROLE_ID', $set['extern_role_id']);
		$this->tpl->setVariable('EXTERN_ROLE', $set['extern_role']);
		$this->tpl->setVariable('ROLES', $set['roles']);
		$this->tpl->setVariable('ER_DESCRIPTION', $set['desc']);
		$this->tpl->setVariable('ACTIONS', $set['actions']);
	}
}
