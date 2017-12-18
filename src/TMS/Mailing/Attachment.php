<?php

/* Copyright (c) 2017 Daniel Weise <daniel.weise@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * An attachment provides an filename.
 * It is used in the Clerk to get the mail attachment.
 */
interface Attachment
{
	/**
	 * Returns the filename of the attachment.
	 *
	 * @return 	string
	 */
	public function getAttachmentFilename();
}
