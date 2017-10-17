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

	public function __construct($parent_gui, $parent_cmd) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();

		$this->g_lng->loadLanguageModule('tms');

		$this->parent_gui = $parent_gui;
		$this->parent_cmd = $parent_cmd;
	}

	public function executeCommand() {
		// TODO: Check if current user may book course for other user here.
		assert('$this->g_user->getId() === $_GET["usr_id"]');

		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];
		global $DIC;
		$process_db = new ilTMSBookingPlayerStateDB();

		$this->init($DIC, $crs_ref_id, $usr_id, $process_db);

		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $this->process($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		$this->g_tpl->show();
	}

	// STUFF FROM Booking\Player

	/**
	 * @inheritdocs
	 */
	protected function getForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormAction($this));
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
		else if ($id == "done") {
			$id = "booking_done";
		}
		return $this->g_lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	protected function redirectToPreviousLocation($message, $success) {
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", null);
		if ($success) {
			ilUtil::sendSuccess($message, true);
		}
		else {
			ilUtil::sendInfo($message, true);
		}
		$this->g_ctrl->redirect($this->parent_gui, $this->parent_cmd);
	}

}

/**
 * cat-tms-patch end
 */
