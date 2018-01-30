<?php

namespace ILIAS\TMS\Timezone;

/**
 * 
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface TimezoneDB {
	/**
	 * Read the dates for given year
	 *
	 * @param string 	$year
	 *
	 * @return sting[]
	 */
	public function readFor($year);

	/**
	 * Creates new entry in source db
	 *
	 * @param string 	$year
	 * @param string 	$start_summer
	 * @param string 	$start_winter
	 *
	 * @return void
	 */
	public function createEntry($year, $start_summer, $start_winter);
}