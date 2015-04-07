<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSuperiorBookingToWaiting extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Participant";
	}
	
	public function _getDescription() {
		return "Participant gets Status 'on waiting list' by Superior-Booking";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B04";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseUsersOnWaitingList();
	}
	
	public function getCC($a_recipient) {
		return $this->maybeSuperiorsCC($a_recipient);
	}
	
	public function getMail($a_recipient) {
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}
		
		return parent::getMail($a_recipient);
	}
}

?>