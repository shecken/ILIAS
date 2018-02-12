<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Wizard;

/**
 * Implementation of the state db over session.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSBookingPlayerStateDB implements Wizard\StateDB {
	/**
	 * @inheritdocs
	 */
	public function load($key) {
		$value = ilSession::get($key);
		if ($value === null) {
			return null;
		}
		$value = json_decode($value, true);
		return new Wizard\ProcessState($key, $value["step_number"], $value["step_data"]);
	}

	/**
	 * @inheritdocs
	 */
	public function save(Wizard\State $state) {
		$key = $state->getWizardId();
		$value =
			[ "step_number" => $state->getStepNumber()
			, "step_data" => $state->getAllStepData()
			];
		ilSession::set($key, json_encode($value));
	}

	/**
	 * @inheritdocs
	 */
	public function delete(Wizard\State $state) {
		$key = $state->getWizardId();
		ilSession::clear($key);
	}
}

/**
 * cat-tms-patch end
 */
