<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Cancel/classes/class.ilTMSCancelPlayerStateDB.php");

/**
 * Displays the TMS booking 
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
abstract class ilTMSCancelGUI  extends Booking\Player {
	use ILIAS\TMS\MyUsersHelper;

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
		// TODO: Check if current user may book course for other user here.
		// assert('$this->g_user->getId() === $_GET["usr_id"]');
		if(!$this->canCancelForUser($_GET["usr_id"])) {
			$this->redirectToPreviousLocation(array("nope"), false);
		}

		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];

		$gui_bindings = new Booking\GUIBindings
			( $this->g_lng
			, $this->g_ctrl
			, $this->parent_gui
			, $this->parent_cmd
			, $this->getPlayerTitle()
			, $this->getConfirmButtonLabel()
			, $this->getOverViewDescription()
			);

		global $DIC;
		$state_db = new Wizard\SessionStateDB();
		$wizard = new Booking\Wizard
			( $DIC
			, $this->getComponentClass()
			, $this->g_user->getId()
			, $crs_ref_id
			, $usr_id
			);
		$player = new Wizard\Player
			( $gui_bindings
			, $wizard
			, $state_db
			);

		$this->setParameter($crs_ref_id, $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $player->run($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		if($this->execute_show) {
			$this->g_tpl->show();
		}
	}

	/**
	 * Get the title of the player.
	 *
	 * @return string
	 */
	protected function getPlayerTitle() {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		if($usr_id === (int)$this->g_user->getId()) {
			return $this->g_lng->txt("canceling");
		}

		require_once("Services/User/classes/class.ilObjUser.php");
		return sprintf($this->g_lng->txt("canceling_for"), ilObjUser::_lookupFullname($usr_id));
	}

	/**
	 * Get a description for the overview step.
	 *
	 * @return string
	 */
	protected function getOverViewDescription() {
		return $this->g_lng->txt("cancel_overview_description");
	}

	/**
	 * Get the label for the confirm button.
	 *
	 * @return string
	 */
	protected function getConfirmButtonLabel() {
		return $this->g_lng->txt("cancel_confirm");
	}

	/**
	 * Is current user allowed to cancel for
	 * Checks the current user is sperior of
	 *
	 * @param int 	$usr_id
	 *
	 * @return bool
	 */
	protected function canCancelForUser($usr_id) {
		if($this->g_user->getId() == $usr_id) {
			return true;
		}

		$employees = $this->getUsersWhereCurrentCanViewBookings((int)$this->g_user->getId());
		return array_key_exists($usr_id, $employees);
	}

}

/**
 * cat-tms-patch end
 */
