<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/class.ilTMSBookingPlayerStateDB.php");
require_once("Services/TMS/Booking/classes/ilTMSBookingGUI.php");

/**
 * Displays the TMS superior booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSuperiorBookingGUI extends \ilTMSBookingGUI {
	/**
	 * @inheritdocs
	 */
	protected function getComponentClass() {
		return Booking\Step::class;
	}

	/**
	 * @inheritdocs
	 */
	protected function setParameter($crs_ref_id, $usr_id) {
		$this->g_ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "usr_id", $usr_id);
	}
}

/**
 * cat-tms-patch end
 */
