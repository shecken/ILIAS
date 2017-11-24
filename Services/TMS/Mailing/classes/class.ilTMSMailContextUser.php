<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * User-related placeholder-values
 */
class ilTMSMailContextUser implements Mailing\MailContext {

	/**
	 * @var int
	 */
	protected $usr_id;

	public function __construct($usr_id) {
		assert('is_int($usr_id)');
		$this->usr_id = $usr_id;
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id) {
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array();
	}
}
