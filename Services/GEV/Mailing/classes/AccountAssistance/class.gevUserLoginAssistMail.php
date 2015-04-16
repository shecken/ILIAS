
<?php

require_once("Services/GEV/Mailing/classes/class.gevAccAssistanceMail.php");
require_once('Services/User/classes/class.ilObjUser.php');

class gevUserLoginAssistMail extends gevAccAssistanceMail {


	public function __construct($logins) {
		parent::__construct( $this->getRecipientUserIDs()
						   , array("LOGIN" => $logins));
	}

	public function getTitle() {
		return "Login assistance";
	}
	
	public function _getDescription() {
		return "Returns logins corresponding to an email";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "A01";
	}
	
	public function getRecipientUserIDs() {
		return ilObjUser::_lookUpId($this->logins[0]);
	}
	
	public function getCC($a_recipient) {
		return null;
	}
	
}

?>
