<?php
require_once(__DIR__."/../vendor/autoload.php");
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/Settings/class.ilReportStudyProgrammeSettingsGUI.php");

use \CaT\Plugins\ReportStudyProgramme;

/**
 * @ilCtrl_isCalledBy ilObjReportStudyProgrammeGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjReportStudyProgrammeGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjReportStudyProgrammeGUI: ilReportStudyProgrammeSettingsGUI, ilIndividualPlanGUI
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjReportStudyProgrammeGUI extends ilObjectPluginGUI
{
	use ReportStudyProgramme\Settings\ilFormHelper;

	const TAB_SHOW_CONTENT = "tab_show_content";
	const TAB_SETTINGS = "tab_settings";

	const SHOW_CONTENT = "showContent";

	/**
	 * @var CaT\Plugins\ReportStudyProgramme\ilActions
	 */
	protected $plugin_actions;

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilAccess, $ilCtrl, $ilTabs, $ilUser, $lng, $tpl;

		$lng->loadLanguageModule("prg");

		$this->g_access = $ilAccess;
		$this->g_ctrl = $ilCtrl;
		$this->g_tabs = $ilTabs;
		$this->g_user = $ilUser;
		$this->g_tpl = $tpl;
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xsp";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		if ($this->g_user->getId() === $_GET["user_id"] || !isset($_GET["user_id"]) || trim($_GET['user_id']) === '') {
			global $ilMainMenu;
			$ilMainMenu->setActive("gev_me_menu");
		}
		$this->plugin_actions = $this->object->getActions();
		$next_class = $this->g_ctrl->getNextClass();
		switch ($next_class) {
			case "ilReportStudyProgrammeSettingsGUI":
				$this->forwardSettings();
				break;
			case 'ilindividualplangui':
				$this->showContent();
				break;
			default:
				$cmd = $this->g_ctrl->getCmd(self::SHOW_CONTENT);
				switch ($cmd) {
					case ilReportStudyProgrammeSettingsGUI::EDIT_SETTINGS:
					case ilReportStudyProgrammeSettingsGUI::SAVE_SETTINGS:
						$this->forwardSettings();
						break;
					case self::SHOW_CONTENT:
						$this->redirectShowContent();
						break;
				}
		}

		$this->g_tpl->setTitle(null);
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($newObj)
	{
		$sp_node_ref_id = $_POST['sp_node_ref_id'];
		$settings = $newObj->getSettings()->withSPNodeRefId($sp_node_ref_id);
		$newObj->setSettings($settings);
		$newObj->update();
		parent::afterSave($newObj);
	}


	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return ilReportStudyProgrammeSettingsGUI::EDIT_SETTINGS;
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
			require_once(__DIR__."/Settings/class.ilReportStudyProgrammeSettingsGUI.php");
			$gui = new \ilReportStudyProgrammeSettingsGUI($actions, $this->plugin->txtClosure());
			$this->g_ctrl->forwardCommand($gui);
		}
	}

	protected function redirectShowContent()
	{
		$this->g_ctrl->redirectByClass('ilindividualplangui', 'showContent');
	}

	public function showContent()
	{
		global $ilLog;
		if (!$this->g_access->checkAccess("visible", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->g_ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->g_tabs->setTabActive(self::TAB_SHOW_CONTENT);
			$settings = $this->object->getSettings();
			require_once("Modules/StudyProgramme/classes/class.ilObjectFactoryWrapper.php");

			if (ilObject::_lookupType($settings->getSPNodeRefId(), true) !== "prg") {
				ilUtil::sendFailure($this->plugin->txt('no_sp_id'), true);
				$this->g_ctrl->redirectByClass("ilReportStudyProgrammeSettingsGUI", "editProperties");
			}
			require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanGUI.php");
			$gui = new \ilIndividualPlanGUI();
			$sp = \ilObjectFactoryWrapper::getInstanceByRefId($settings->getSPNodeRefId());
			$assignments = $sp->getAssignmentsOf($this->g_user->getId());
			if (count($assignments) < 1) {
				ilUtil::sendFailure($this->plugin->txt('no_assignment'), true);
				return;
			}
			$assignment = $assignments[0];
			$gui->setAssignmentId($assignment->getId());
			$gui->setUserId($this->g_user->getId());
			$gui->setSPRefId($settings->getSPNodeRefId());
			$this->g_ctrl->forwardCommand($gui);
		}
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
			$this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("properties"), $this->g_ctrl->getLinkTarget($this, ilReportStudyProgrammeSettingsGUI::EDIT_SETTINGS));
		}

		$this->addPermissionTab();
	}
}
