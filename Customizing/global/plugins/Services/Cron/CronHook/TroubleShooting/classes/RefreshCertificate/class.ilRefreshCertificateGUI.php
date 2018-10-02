<?php

require_once __DIR__."/../PluginLanguage.php";
require_once "class.ilRefreshAllGUI.php";
require_once "class.ilRefreshSomeGUI.php";

/**
 * @ilCtrl_Calls ilRefreshCertificateGUI: ilRefreshAllGUI
 * @ilCtrl_Calls ilRefreshCertificateGUI: ilRefreshSomeGUI
 */
class ilRefreshCertificateGUI {
	use PluginLanguage;

	const CMD_REFRESH_CERTIFICATE = "refresh_certificate";

	const SUBTAB_REFRESH_ALL = "refresh_all";
	const SUBTAB_REFRESH_SOME = "refresh_some";

	public function __construct(
		ilCtrl $ctrl,
		ilTabsGUI $tabs,
		ilTemplate $tpl,
		Closure $txt,
		RefreshCertificateHelper $helper
	) {
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->txt = $txt;
		$this->helper = $helper;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();

		switch($next_class) {
			case "ilrefreshallgui":
				$this->setSubTabs(self::SUBTAB_REFRESH_ALL);
				$gui = new ilRefreshAllGUI($this->ctrl, $this->tpl, $this->txt, $this->helper);
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilrefreshsomegui":
				$this->setSubTabs(self::SUBTAB_REFRESH_SOME);
				$gui = new ilRefreshSomeGUI($this->ctrl, $this->tpl, $this->txt, $this->helper);
				$this->ctrl->forwardCommand($gui);
				break;
			default:
				switch($cmd) {
					case self::CMD_REFRESH_CERTIFICATE:
						$this->redirectRefreshAll();
						break;
					default:
						throw new Exception("Unknown command: ".$cmd);
				}
		}
	}

	protected function redirectRefreshAll() {
		$link = $this->ctrl->getLinkTargetByClass(
			array("ilRefreshAllGUI"),
			ilRefreshAllGUI::CMD_SHOW_FORM,
			'',
			false,
			false
		);
		ilUtil::redirect($link);
	}

	protected function setSubTabs($active_tab)
	{
		$link = $this->ctrl->getLinkTargetByClass("ilRefreshAllGUI", ilRefreshAllGUI::CMD_SHOW_FORM);
		$this->tabs->addSubTab(self::SUBTAB_REFRESH_ALL, $this->txt(self::SUBTAB_REFRESH_ALL), $link);

		$link = $this->ctrl->getLinkTargetByClass("ilRefreshSomeGUI", ilRefreshSomeGUI::CMD_SHOW_FORM);
		$this->tabs->addSubTab(self::SUBTAB_REFRESH_SOME, $this->txt(self::SUBTAB_REFRESH_SOME), $link);

		$this->tabs->activateSubTab($active_tab);
	}
}