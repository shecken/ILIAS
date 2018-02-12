<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Wizard;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/class.ilTMSBookingPlayerStateDB.php");

/**
 * GUI Bindings for TMS-Booking process.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSBookingGUIBinding extends Wizard\GUIBindings {
	/**
	 * @var	ilLanguage
	 */
	protected $lng;

	/**
	 * @var	ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var	mixed
	 */
	protected $parent_gui;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	/**
	 * @var string
	 */
	protected $player_title;

	/**
	 * @var string
	 */
	protected $confirm_button_label;

	/**
	 * @var string
	 */
	protected $overview_description;

	final public function __construct(\ilLanguage $lng, \ilCtrl $ctrl, $parent_gui, $parent_cmd, $player_title, $confirm_button_label, $overview_description) {
		assert('is_object($parent_gui)');
		assert('is_object($parent_cmd)');
		assert('is_string($confirm_button_label)');
		assert('is_string($player_title)');
		assert('is_string($overview_description)');
		$this->lng = $lng;
		$this->ctrl = $ctrl;
		$this->lng->loadLanguageModule('tms');
		$this->parent_gui = $parent_gui;
		$this->parent_cmd = $parent_cmd;
		$this->player_title = $player_title;
		$this->confirm_button_label = $confirm_button_label;
		$this->overview_description = $overview_description;
	}

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
		else if ($id == "title") {
			return $this->player_title;
		}
		else if ($id == "confirm") {
			return $this->confirm_button_label;
		}
		else if ($id == "overview_description") {
			return $this->overview_description;
		}
		return $this->g_lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	protected function redirectToPreviousLocation($messages, $success) {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		$this->setParameter(null, null);
		$this->ctrl->setParameter($this->parent_gui, "s_user", $usr_id);

		if (count($messages)) {
			$message = join("<br/>", $messages);
			if ($success) {
				ilUtil::sendSuccess($message, true);
			}
			else {
				ilUtil::sendInfo($message, true);
			}
		}
		$this->ctrl->redirect($this->parent_gui, $this->parent_cmd);
	}
}

/**
 * cat-tms-patch end
 */
