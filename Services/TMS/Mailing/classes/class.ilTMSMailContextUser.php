<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * User-related placeholder-values
 */
class ilTMSMailContextUser implements Mailing\MailContext {
/*
	'MAIL_SALUTATION'
    'FIRST_NAME'
    'LAST_NAME'
    'LOGIN'
    'ILIAS_URL'
    'CLIENT_NAME'
    'COURSE_TITLE'
    'COURSE_LINK'
*/

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
		switch($placeholder_id) {
			case 'MAIL_SALUTATION':
				return 'Hr. ';

			case 'LOGIN':
				return 'loginlogin';


			default:
				return null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array();
	}
}
