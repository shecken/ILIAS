<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevEffectivenessAnalysisFirstReminder extends gevCrsAutoMail {
	public function getTitle() {
		return "Reminder Superior";
	}
	
	public function _getDescription() {
		return "Superiors get reminded to have access to effectiveness analysis by now";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "S01";
	}
	
	public function getRecipientUserIDs() {
		return array();
	}
	
	public function getCC($a_recipient) {
		return array();
	}

	protected function getBCC($a_recipient) {
		return array();
	}

	protected function log($a_mail, $a_occasion) {
	}
}

?>