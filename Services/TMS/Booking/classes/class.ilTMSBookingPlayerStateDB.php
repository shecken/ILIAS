<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

/**
 * Implementation of the state db over session.
 *
 * TODO: implement me!
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSBookingPlayerStateDB implements Booking\ProcessStateDB {
	/**
	 * @inheritdocs
	 */
	public function load($crs_id, $usr_id) {
		return null;
	}

	/**
	 * @inheritdocs
	 */
	public function save(Booking\ProcessState $state) {
	}

	/**
	 * @inheritdocs
	 */
	public function delete(Booking\ProcessState $state) {
	}
}

/**
 * cat-tms-patch end
 */
