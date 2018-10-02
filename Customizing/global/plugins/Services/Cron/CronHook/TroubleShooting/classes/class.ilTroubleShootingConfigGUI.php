<?php

require_once "Services/Component/classes/class.ilPluginConfigGUI.php";
require_once __DIR__."/DeleteUserFromCourse/class.ilDeleteUserFromCourseGUI.php";
require_once __DIR__."/ParticipantList/class.ilParticipantListGUI.php";
require_once __DIR__."/SetParticipationStatus/class.ilSetParticipationStatusGUI.php";
require_once __DIR__."/TransferUser/class.ilTransferUserGUI.php";
require_once __DIR__."/RefreshCertificate/class.ilRefreshCertificateGUI.php";

/**
 * @ilCtrl_Calls ilTroubleShootingConfigGUI: ilDeleteUserFromCourseGUI
 * @ilCtrl_Calls ilTroubleShootingConfigGUI: ilParticipantListGUI
 * @ilCtrl_Calls ilTroubleShootingConfigGUI: ilSetParticipationStatusGUI
 * @ilCtrl_Calls ilTroubleShootingConfigGUI: ilTransferUserGUI
 * @ilCtrl_Calls ilTroubleShootingConfigGUI: ilRefreshCertificateGUI
 * @ilCtrl_isCalledBy ilTroubleShootingConfigGUI: ilObjComponentSettingsGUI
 */

class ilTroubleShootingConfigGUI extends ilPluginConfigGUI
{
	const CMD_CONFIGURE = "configure";
	const TAB_DELETE_USER = "delete_user";
	const TAB_PARTICIPANT_LIST = "participant_list";
	const TAB_PARTICIPATION_STATUS = "participation_status";
	const TAB_TRANSFER_USER = "transfer_user";
	const TAB_REFRESH_CERTIFICATE = "refresh_certificate";

	public function __construct()
	{
		global $ilCtrl, $ilTabs, $tpl, $lng;
		$this->g_tabs = $ilTabs;
		$this->g_ctrl = $ilCtrl;
		$this->g_tpl = $tpl;
		$this->g_lng = $lng;
	}

	public function performCommand($cmd)
	{
		$this->setTabs();
		$next_class = $this->g_ctrl->getNextClass();
		switch ($next_class) {
			case "ildeleteuserfromcoursegui":
				$this->setTabActive(self::TAB_DELETE_USER);
				$actions = $this->plugin_object->getDeleteUserActions();
				$gui = new ilDeleteUserFromCourseGUI(
					$this->g_ctrl,
					$this->g_tpl,
					$this->plugin_object->getTxtClosure(),
					$actions
				);
				$this->g_ctrl->forwardCommand($gui);
				break;
			case "ilparticipantlistgui":
				$this->setTabActive(self::TAB_PARTICIPANT_LIST);
				$gui = new ilParticipantListGUI(
					$this->g_ctrl,
					$this->g_tabs,
					$this->g_tpl,
					$this->plugin_object
				);
				$this->g_ctrl->forwardCommand($gui);
				break;
			case "ilsetparticipationstatusgui":
				$this->setTabActive(self::TAB_PARTICIPATION_STATUS);
				$gui = new ilSetParticipationStatusGUI(
					$this->g_ctrl,
					$this->g_tabs,
					$this->g_tpl,
					$this->plugin_object->getTxtClosure(),
					null
				);
				$this->g_ctrl->forwardCommand($gui);
				break;
			case "iltransferusergui":
				$this->setTabActive(self::TAB_TRANSFER_USER);
				$gui = new ilTransferUserGUI(
					$this->g_ctrl,
					$this->g_tabs,
					$this->g_tpl,
					$this->plugin_object->getTxtClosure(),
					$this->plugin_object->getTransferActions()
				);
				$this->g_ctrl->forwardCommand($gui);
				break;
			case "ilrefreshcertificategui":
				$this->setTabActive(self::TAB_REFRESH_CERTIFICATE);
				require_once __DIR__."/RefreshCertificate/RefreshCertificateHelper.php";
				$transer_actions = $this->plugin_object->getTransferActions();
				$gui = new ilRefreshCertificateGUI(
					$this->g_ctrl,
					$this->g_tabs,
					$this->g_tpl,
					$this->plugin_object->getTxtClosure(),
					new RefreshCertificateHelper()
				);
				$this->g_ctrl->forwardCommand($gui);
				break;
			default:
				switch($cmd) {
					case self::CMD_CONFIGURE:
						$this->redirectToFirstTab();
						break;
					default:
						throw new Exception("Unknown command: ".$cmd);
						break;
				}
		}
	}

	protected function redirectToFirstTab()
	{
		$link = $this->g_ctrl->getLinkTargetByClass("ilDeleteUserFromCourseGUI",
			ilDeleteUserFromCourseGUI::CMD_SHOW_FORM,
			'',
			false,
			false
		);
		ilUtil::redirect($link);
	}

	protected function setTabs()
	{
		$this->g_tabs->addTab(
			self::TAB_DELETE_USER,
			$this->plugin_object->txt(self::TAB_DELETE_USER),
			$this->g_ctrl->getLinkTargetByClass('ilDeleteUserFromCourseGUI', ilDeleteUserFromCourseGUI::CMD_SHOW_FORM)
		);

		$this->g_tabs->addTab(
			self::TAB_PARTICIPANT_LIST,
			$this->plugin_object->txt(self::TAB_PARTICIPANT_LIST),
			$this->g_ctrl->getLinkTargetByClass('ilParticipantListGUI', ilParticipantListGUI::CMD_EDIT_PARTICIPANT_LIST)
		);

		$this->g_tabs->addTab(
			self::TAB_PARTICIPATION_STATUS,
			$this->plugin_object->txt(self::TAB_PARTICIPATION_STATUS),
			$this->g_ctrl->getLinkTargetByClass('ilSetParticipationStatusGUI', ilSetParticipationStatusGUI::CMD_SHOW_FORM)
		);

		$this->g_tabs->addTab(
			self::TAB_TRANSFER_USER,
			$this->plugin_object->txt(self::TAB_TRANSFER_USER),
			$this->g_ctrl->getLinkTargetByClass('ilTransferUserGUI', ilTransferUserGUI::CMD_SHOW_FORM)
		);

		$this->g_tabs->addTab(
			self::TAB_REFRESH_CERTIFICATE,
			$this->plugin_object->txt(self::TAB_REFRESH_CERTIFICATE),
			$this->g_ctrl->getLinkTargetByClass('ilRefreshCertificateGUI', ilRefreshCertificateGUI::CMD_REFRESH_CERTIFICATE)
		);
	}

	protected function setTabActive($tab)
	{
		$this->g_tabs->setTabActive($tab);
	}
}
