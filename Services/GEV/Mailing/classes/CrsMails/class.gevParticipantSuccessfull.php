<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantSuccessfull extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Participant";
	}
	
	public function _getDescription() {
		return "Participant gets Status 'successful'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F01";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseSuccessfullParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>