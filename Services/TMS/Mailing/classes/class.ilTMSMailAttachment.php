<?php

use ILIAS\TMS\Mailing;

/**
 * Class ilTMSMailContextAttachment
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilTMSMailAttachment implements Mailing\Attachment
{
	/**
	 * Constructor of the class ilTMSMailContextAttachment
	 */
	public function __construct()
	{
		
	}

	/**
	 * @inheritdoc
	 */
	public function getAttachmentPath()
	{
		return $this->attachment_path;
	}
}