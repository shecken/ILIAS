<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantTrainingCancelled extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Participant";
	}
	
	public function _getDescription() {
		return "Training gets cancelled by Admin";
	}
	
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C09";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseTrainers();
	}
	
	public function getCC($a_recipient) {
		return $this->maybeSuperiorsCC($a_recipient);
	}
}

?>