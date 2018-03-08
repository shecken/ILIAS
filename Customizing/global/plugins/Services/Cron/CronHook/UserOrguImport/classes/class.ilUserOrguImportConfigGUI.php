<?php

require_once 'Services/Component/classes/class.ilPluginConfigGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilExternRolesConfiguratorGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilUserOrguImportLogGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilDeployedUserOrguDataGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilUserConfiguratorGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilOrguConfiguratorGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilFilesystemConfiguratorGUI.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilFunctionRoleAssignmentConfiguratorGUI.php';#
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/class.ilExitUserConfiguratorGUI.php';

/**
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilExternRolesConfiguratorGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilDeployedUserOrguDataGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilUserOrguImportLogGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilUserConfiguratorGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilOrguConfiguratorGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilFilesystemConfiguratorGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilFunctionRoleAssignmentConfiguratorGUI
 * @ilCtrl_Calls ilUserOrguImportConfigGUI: ilExitUserConfiguratorGUI
 * @ilCtrl_isCalledBy ilUserOrguImportConfigGUI: ilObjComponentSettingsGUI
 */

class ilUserOrguImportConfigGUI extends ilPluginConfigGUI
{
	public function __construct()
	{
		global $DIC;
		$this->tabs_gui = $DIC['ilTabs'];
		$this->ctrl = $DIC['ilCtrl'];
	}


	public function performCommand($cmd)
	{
		$this->getTabs();
		$next_class = $this->ctrl->getNextClass();
		switch ($next_class) {
			case 'ilexternrolesconfiguratorgui':
				$this->externRolesConfiguration();
				break;
			case 'ildeployeduserorgudatagui':
				$this->deployedUserOrguData();
				break;
			case 'iluserorguimportloggui':
				$this->viewLog();
				break;
			case 'iluserconfiguratorgui':
				$this->userConfig();
				break;
			case 'ilorguconfiguratorgui':
				$this->orguConfig();
				break;
			case 'ilfilesystemconfiguratorgui':
				$this->filesystemConfig();
				break;
			case 'ilfunctionroleassignmentconfiguratorgui':
				$this->functionRoleAssignmentConfig();
				break;
			case 'ilexituserconfiguratorgui':
				$this->exitUserConfig();
				break;
			default:
				$this->externRolesConfiguration();
				break;
		}
		return true;
	}

	protected function externRolesConfiguration()
	{
		$this->tabs_gui->setTabActive('extern_roles_configuration');
		$gui = new ilExternRolesConfiguratorGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function deployedUserOrguData()
	{
		$this->tabs_gui->setTabActive('deployed_files_overview');
		$gui = new ilDeployedUserOrguDataGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function viewLog()
	{
		$this->tabs_gui->setTabActive('view_log');
		$gui = new ilUserOrguImportLogGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function userConfig()
	{
		$this->tabs_gui->setTabActive('user_config');
		$gui = new ilUserConfiguratorGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function orguConfig()
	{
		$this->tabs_gui->setTabActive('orgu_config');
		$gui = new ilOrguConfiguratorGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function filesystemConfig()
	{
		$this->tabs_gui->setTabActive('fs_config');
		$gui = new ilFilesystemConfiguratorGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function functionRoleAssignmentConfig()
	{
		$this->tabs_gui->setTabActive('function_role_config');
		$gui = new ilFunctionRoleAssignmentConfiguratorGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	protected function exitUserConfig()
	{
		$this->tabs_gui->setTabActive('exit_user_configuration');
		$gui = new ilExitUserConfiguratorGUI($this->getPluginObject(), $this);
		$this->ctrl->forwardCommand($gui);
	}

	public function getTabs()
	{
		$this->tabs_gui->addTab(
			'extern_roles_configuration',
			$this->txt('extern_roles_configuration'),
			$this->getLinkTarget('extern_roles_configuration')
		);
		$this->tabs_gui->addTab(
			'deployed_files_overview',
			$this->txt('deployed_files_overview'),
			$this->getLinkTarget('deployed_files_overview')
		);
		$this->tabs_gui->addTab(
			'user_config',
			$this->txt('udf_config'),
			$this->getLinkTarget('user_config')
		);
		$this->tabs_gui->addTab(
			'orgu_config',
			$this->txt('orgu_config'),
			$this->getLinkTarget('orgu_config')
		);
		$this->tabs_gui->addTab(
			'fs_config',
			$this->txt('fs_config'),
			$this->getLinkTarget('fs_config')
		);
		$this->tabs_gui->addTab(
			'function_role_config',
			$this->txt('function_role_config'),
			$this->getLinkTarget('function_role_config')
		);
		$this->tabs_gui->addTab(
			'view_log',
			$this->txt('view_log'),
			$this->getLinkTarget('view_log')
		);
		$this->tabs_gui->addTab(
			'exit_user_config',
			$this->txt('exit_user_config'),
			$this->getLinkTarget('exit_user_config')
		);
	}

	protected function getLinkTarget($target)
	{
		switch ($target) {
			case 'extern_roles_configuration':
				return $this->ctrl->getLinkTargetByClass('ilExternRolesConfiguratorGUI', 'show');
			case 'deployed_files_overview':
				return $this->ctrl->getLinkTargetByClass('ilDeployedUserOrguDataGUI', 'show');
			case 'view_log':
				return $this->ctrl->getLinkTargetByClass('ilUserOrguImportLogGUI', 'show_filter');
			case 'user_config':
				return $this->ctrl->getLinkTargetByClass('ilUserConfiguratorGUI', ilUserConfiguratorGUI::CMD_SHOW_UDF_CONFIG);
			case 'orgu_config':
				return $this->ctrl->getLinkTargetByClass('ilOrguConfiguratorGUI', ilOrguConfiguratorGUI::CMD_SHOW);
			case 'fs_config':
				return $this->ctrl->getLinkTargetByClass('ilFilesystemConfiguratorGUI', ilFilesystemConfiguratorGUI::CMD_SHOW);
			case 'function_role_config':
				return $this->ctrl->getLinkTargetByClass('ilFunctionRoleAssignmentConfiguratorGUI', ilFunctionRoleAssignmentConfiguratorGUI::CMD_SHOW);
			case 'exit_user_config':
				return $this->ctrl->getLinkTargetByClass('ilExitUserConfiguratorGUI', ilExitUserConfiguratorGUI::CMD_SHOW);
		}
	}

	protected function txt($key)
	{
		return $this->getPluginObject()->txt($key);
	}
}
