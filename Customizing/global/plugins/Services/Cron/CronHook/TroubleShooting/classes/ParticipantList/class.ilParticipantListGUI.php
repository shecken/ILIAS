<?php

require_once __DIR__."/AddList/class.ilAddListGUI.php";
require_once __DIR__."/AddList/ilAddListActions.php";
require_once __DIR__."/ChangeList/class.ilChangeListGUI.php";
require_once __DIR__."/ChangeList/ilChangeListActions.php";
require_once __DIR__."/../PluginLanguage.php";

/**
 * @ilCtrl_Calls ilParticipantListGUI: ilAddListGUI, ilChangeListGUI
 */
class ilParticipantListGUI {
	use PluginLanguage;

	const CMD_EDIT_PARTICIPANT_LIST = "edit_participant_list";
	const SUBTAB_CHANGE_LIST = "change_list";
	const SUBTAB_ADD_LIST = "add_list";

	public function __construct(ilCtrl $ctrl,
		ilTabsGUI $tabs,
		ilTemplate $tpl,
		ilTroubleShootingPlugin $plugin
	) {
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->plugin = $plugin;
		$this->txt = $plugin->getTxtClosure();
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();

		switch($next_class) {
			case "iladdlistgui":
				$this->setSubTabs(self::SUBTAB_ADD_LIST);
				$actions = $this->plugin->getAddListActions();
				$gui = new ilAddListGUI($this, $this->ctrl, $this->tpl, $this->txt, $actions);
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilchangelistgui":
				$this->setSubTabs(self::SUBTAB_CHANGE_LIST);
				$actions = $this->plugin->getChangeListActions();
				$gui = new ilChangeListGUI($this, $this->ctrl, $this->tpl, $this->txt, $actions);
				$this->ctrl->forwardCommand($gui);
				break;
			default:
				switch($cmd) {
					case self::CMD_EDIT_PARTICIPANT_LIST:
						$this->redirectChangeList();
						break;
					default:
						throw new Exception("Unknown command: ".$cmd);
				}
		}
	}

	protected function redirectChangeList() {
		$link = $this->ctrl->getLinkTargetByClass(
			array("ilChangeListGUI"),
			ilChangeListGUI::CMD_SHOW_FORM,
			'',
			false,
			false
		);
		ilUtil::redirect($link);
	}

	protected function setSubTabs($active_tab)
	{
		$link = $this->ctrl->getLinkTargetByClass("ilChangeListGUI", ilChangeListGUI::CMD_SHOW_FORM);
		$this->tabs->addSubTab(self::SUBTAB_CHANGE_LIST, $this->txt(self::SUBTAB_CHANGE_LIST), $link);

		$link = $this->ctrl->getLinkTargetByClass("ilAddListGUI", ilAddListGUI::CMD_SHOW_FORM);
		$this->tabs->addSubTab(self::SUBTAB_ADD_LIST, $this->txt(self::SUBTAB_ADD_LIST), $link);

		$this->tabs->activateSubTab($active_tab);
	}
}