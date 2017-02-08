<?php
require_once(__DIR__."/../vendor/autoload.php");
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/Settings/class.ilVAPassSettingsGUI.php");

use \CaT\Plugins\ReportVAPass;

/**
 * @ilCtrl_isCalledBy ilObjReportVAPassGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjReportVAPassGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjReportVAPassGUI: ilVAPassSettingsGUI
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjReportVAPassGUI extends ilObjectPluginGUI
{
	use ReportVAPass\Settings\ilFormHelper;

	const TAB_SHOW_CONTENT = "tab_show_content";
	const TAB_SETTINGS = "tab_settings";

	const SHOW_CONTENT = "showContent";

	/**
	 * @var CaT\Plugins\ReportVAPass\ilActions
	 */
	protected $plugin_actions;

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilAccess, $ilCtrl, $ilTabs;

		$this->g_access = $ilAccess;
		$this->g_ctrl = $ilCtrl;
		$this->g_tabs = $ilTabs;
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xvap";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		$this->plugin_actions = $this->object->getActions();
		$next_class = $this->g_ctrl->getNextClass();

		switch ($next_class) {
			case "ilvapasssettingsgui":
				$this->forwardSettings();
				break;
			default:
				switch ($cmd) {
					case ilVAPassSettingsGUI::EDIT_SETTINGS:
					case ilVAPassSettingsGUI::SAVE_SETTINGS:
						$this->forwardSettings();
						break;
					case self::SHOW_CONTENT:
						$this->showContent();
						break;
				}
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return ilVAPassSettingsGUI::EDIT_SETTINGS;
	}

	/**
	 * Get standard command
	 */
	public function getStandardCmd()
	{
		return "view";
	}

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);
		$this->addSettingsFormItems($form);

		return $form;
	}

	protected function forwardSettings()
	{
		if (!$this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->g_tabs->setTabActive(self::TAB_SETTINGS);
			$actions = $this->object->getActions();
			require_once(__DIR__."/Settings/class.ilVAPassSettingsGUI.php");
			$gui = new \ilVAPassSettingsGUI($actions, $this->plugin->txtClosure());
			$this->g_ctrl->forwardCommand($gui);
		}
	}

	public function showContent()
	{
		$this->g_tabs->setTabActive(self::TAB_SHOW_CONTENT);
	}

		/**
	 * Set tabs
	 */
	protected function setTabs()
	{
		if ($this->g_access->checkAccess("visible", "", $this->object->getRefId())) {
			$this->g_tabs->addTab(self::TAB_SHOW_CONTENT, $this->txt("view"), $this->g_ctrl->getLinkTarget($this, self::SHOW_CONTENT));
		}

		if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
			$this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("properties"), $this->g_ctrl->getLinkTarget($this, ilVAPassSettingsGUI::EDIT_SETTINGS));
		}

		$this->addPermissionTab();
	}
}
