<?php

require_once("Services/GEV/Mailing/classes/class.gevAccAssistanceMail.php");
require_once('Services/User/classes/class.ilObjUser.php');

class gevPWAssistMail extends gevAccAssistanceMail {
	
	protected $usr;

	public function __construct(ilObjUser $usr, $pwassist_link) {
		$this->usr = $usr;
		parent::__construct( $this->getRecipientUserID()
						   , array("PWASSIST_LINK" => $pwassist_link,"LOGIN" => array($this->usr->getLogin()));
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
	
	public function getRecipientUserID() {
		return $this->usr->getId();
	}
	
	public function getCC($a_recipient) {
		return null;
	}
	
}

?>