<?php

require_once 'Services/Component/classes/class.ilPluginConfigGUI.php';
require_once __DIR__."/Configuration/class.FileConfigurationGUI.php";

/**
 * @ilCtrl_Calls ilDiMAkImportConfigGUI: FileConfigurationGUI
 * @ilCtrl_isCalledBy ilUserOrguImportConfigGUI: ilObjComponentSettingsGUI
 */

class ilDiMAkImportConfigGUI extends ilPluginConfigGUI
{
	const CMD_CONFIGURE = "configure";
	const TAB_FILE_CONFIGURATION = "tab_file_configuration";

	public function __construct()
	{
		global $DIC;
		$this->g_tabs = $DIC['ilTabs'];
		$this->g_ctrl = $DIC['ilCtrl'];
		$this->g_tpl = $DIC['tpl'];
	}


	public function performCommand($cmd)
	{
		$this->setTabs();
		$next_class = $this->g_ctrl->getNextClass();

		switch($next_class) {
			case "fileconfigurationgui":
				//$this->activateTab(self::TAB_FILE_CONFIGURATION);
				$gui = new FileConfigurationGUI($this->g_ctrl, $this->g_tpl, $this->getPluginObject());
				$this->g_ctrl->forwardCommand($gui);
				break;
			default:
				switch($cmd) {
					case self::CMD_CONFIGURE:
						$this->redirectFileConfiguration();
						break;
					default:
						throw new Exception("Unknown command: ".$cmd);
				}
		}
	}

	protected function redirectFileConfiguration()
	{
		$link = $this->g_ctrl->getLinkTargetByClass(
			"FileConfigurationGUI",
			FileConfigurationGUI::CMD_SHOW_CONFIGURATION,
			"",
			false,
			false
		);

		ilUtil::redirect($link);
	}

	protected function setTabs()
	{
		$link = $this->g_ctrl->getLinkTargetByClass(
			"FileConfigurationGUI",
			FileConfigurationGUI::CMD_SHOW_CONFIGURATION
		);
		$this->g_tabs->addTab(
			self::TAB_FILE_CONFIGURATION,
			$this->getPluginObject()->txt(self::TAB_FILE_CONFIGURATION),
			$link
		);
	}

	protected function activateTab($tab)
	{
		$this->g_tabs->setActiveTab($tab);
	}
}
