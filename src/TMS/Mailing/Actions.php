<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This encapsulates basic functionality for mailing.
 */
interface Actions {
	/**
	 * Sends a mail for a course to the user.
	 */
	public function sendCourseMail($mail_id, $crs_ref_id, $user_id);
}
