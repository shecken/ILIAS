<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * Recipients for mail.
 *
 */
interface MailRecipient {

	/**
	 * Get the mail-address.
	 *
	 * @return string
	 */
	public function getRecipientMail();

	/**
	 * Get the mail-address.
	 *
	 * @return string
	 */
	public function getRecipientMail();

	/**
	 * Get the id of the recipient.
	 *
	 * @return string | null
	 */
	public function getRecipientId();



}
