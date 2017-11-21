<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This encapsulates basic functionality for mailing.
 */
interface Actions {
	const BOOKED_ON_COURSE = "B01";
	const BOOKED_ON_WAITINGLIST = "B02";
	const CANCELED_FROM_COURSE = "C01";
	const CANCELED_FROM_WAITINGLIST = "C02";

	/**
	 * Sends a mail for a course to the user.
	 */
	public function sendCourseMail($mail_id, $crs_ref_id, $user_id);
}
