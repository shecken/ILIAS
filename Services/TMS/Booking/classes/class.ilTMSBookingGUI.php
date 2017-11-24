<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/class.ilTMSBookingPlayerStateDB.php");

/**
 * Displays the TMS booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSBookingGUI  extends Booking\Player {
	use \ILIAS\TMS\MyUsersHelper;

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	/**
	 * @var	ilLanguage
	 */
	protected $g_lng;

	/**
	 * @var	mixed
	 */
	protected $parent_gui;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	public function __construct($parent_gui, $parent_cmd, $execute_show = true) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();

		$this->g_lng->loadLanguageModule('tms');

		$this->parent_gui = $parent_gui;
		$this->parent_cmd = $parent_cmd;

		/**
		 * ToDo: Remove this flag.
		 * It's realy ugly, but we need it. If we get here by a plugin parent
		 * the plugin executes show by him self. So we don't need it here
		 */
		$this->execute_show = $execute_show;
	}

	public function executeCommand() {
		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];

		if((int)$this->g_user->getId() !== $usr_id && !$this->checkIsSuperiorEmployeeBelowCurrent($usr_id)) {
			$this->redirectToPreviousLocation(array($this->g_lng->txt("no_permissions_to_book")), false);
		}

		global $DIC;
		$process_db = new ilTMSBookingPlayerStateDB();

		$this->init($DIC, $crs_ref_id, $usr_id, $process_db);

		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $this->process($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		if($this->execute_show) {
			$this->g_tpl->show();
		}
	}

	// STUFF FROM Booking\Player

	/**
	 * @inheritdocs
	 */
	protected function getForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormAction($this));
		$form->setShowTopButtons(true);
		return $form;
	}

	/**
	 * @inheritdocs
	 */
	protected function txt($id) {
		if ($id === "abort") {
			$id = "cancel";
		}
		else if ($id === "next") {
			$id = "btn_next";
		}
		else if ($id == "aborted") {
			$id = "booking_aborted";
		}
		else if ($id == "previous") {
			$id = "btn_previous";
		}
		return $this->g_lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	protected function redirectToPreviousLocation($messages, $success) {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", null);
		$this->g_ctrl->setParameter($this->parent_gui, "s_user", $usr_id);

		if (count($messages)) {
			$message = join("<br/>", $messages);
			if ($success) {
				ilUtil::sendSuccess($message, true);
			}
			else {
				ilUtil::sendInfo($message, true);
			}
		}
		$this->g_ctrl->redirect($this->parent_gui, $this->parent_cmd);
	}

	/**
	 * @inheritdocs
	 */
	protected function getPlayerTitle() {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		if($usr_id === (int)$this->g_user->getId()) {
			return $this->g_lng->txt("booking");
		}

		require_once("Services/User/classes/class.ilObjUser.php");
		return sprintf($this->g_lng->txt("booking_for"), ilObjUser::_lookupFullname($usr_id));
	}

	/**
	 * @inheritdocs
	 */
	protected function getOverViewDescription() {
		return $this->g_lng->txt("booking_overview_description");
	}

	/**
	 * @inheritdocs
	 */
	protected function getConfirmButtonLabel() {
		return $this->g_lng->txt("booking_confirm");
	}

	/**
	 * Checks if a user is hierarchically under the current user.
	 *
	 * @param int 	$usr_id
	 *
	 * @return bool
	 */
	protected function checkIsSuperiorEmployeeBelowCurrent($usr_id) {
		$members_below = $this->getUserWhereCurrentCanBookFor($this->g_user->getId());
		return array_key_exists($usr_id, $members_below);
	}

}

/**
 * cat-tms-patch end
 */
