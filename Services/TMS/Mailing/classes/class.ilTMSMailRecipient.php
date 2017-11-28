<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

require_once('./Services/User/classes/class.ilObjUser.php');
/**
 * recipients for mails
 */
class ilTMSMailRecipient implements Mailing\Recipient {

	/**
	 * @var int
	 */
	protected $usr_id;

	public function __construct($usr_id = null) {
		assert('is_int($usr_id) || $usr_id===null');
		$this->usr_id = $usr_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getMailAddress() {
		if($this->usr_id) {
			return \ilObjUser::_lookupEmail($this->usr_id);
		}
		//raise
	}

	/**
	 * @inheritdoc
	 */
	public function getUserId() {
		return $this->usr_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getUserName() {
		if($this->usr_id) {
			$nam = \ilObjUser::_lookupName($this->usr_id);
			return trim(sprintf('%s %s %s',
				$nam['title'],
				$nam['firstname'],
				$nam['lastname']
			));
		}
		return null;
	}
}
