<?php

require_once("Services/GEV/Mailing/classes/class.gevAccAssistanceMail.php");
require_once('Services/User/classes/class.ilObjUser.php');

class gevPWAssistMail extends gevAccAssistanceMail {
	private $usr;

	public function __construct(ilObjUser $usr, $pwassist_link) {
		$this->usr = $usr;
		parent::__construct( $this->getRecipientUserIDs()
						   , array("PWASSIST_LINK" => $pwassist_link));
	}

	public function getTitle() {
		return "PW assistance";
	}
	
	public function _getDescription() {
		return "Sends an email containing a link to change the password";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "A02";
	}
	
	public function getRecipientUserIDs() {
		return $this->usr->getId();
	}
	
	public function getCC($a_recipient) {
		return null;
	}
	
}

?>