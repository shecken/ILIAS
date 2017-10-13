<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/TMS/TrainingSearch/classes/Helper.php");

/**
 * Implementation of the Booking Player in ILIAS context.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSBookingPlayerGUI extends Booking\Player {
	/**
	 */
	protected function getForm() {
	}

	/**
	 */
	protected function txt($id) {
	}

	/**
	 */
	protected function redirectToPreviousLocation($message) {
	}
}

/**
 * cat-tms-patch end
 */
