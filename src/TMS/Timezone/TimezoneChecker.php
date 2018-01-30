<?php

namespace ILIAS\TMS\Timezone;

/**
 * 
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface TimezoneChecker {
	/**
	 * Checks the given date is in summer timezone
	 *
	 * @param string 	$date
	 *
	 * @return bool
	 */
	public function isSummerTimeZone($date);

	/**
	 * Checks the given date is in winter timezone
	 *
	 * @param string 	$date
	 *
	 * @return bool
	 */
	public function isWinterTimeZone($date);
}