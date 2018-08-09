<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevAdminCancelBookedToCancelledWithCosts extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		// gev-patch start gev_3708
		return "Teilnehmer (gebucht) erhält Buchungsstatus 'Mit Kosten storniert' durch Stornierung durch Admin";
		// gev-patch end gev_3708
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C06";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseCancelledWithCostsMembers();
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