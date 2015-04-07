<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantAbsentNotExcused extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Participant";
	}
	
	public function _getDescription() {
		return "Participant gets Status 'absent not excused'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F03";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseAbsentParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>