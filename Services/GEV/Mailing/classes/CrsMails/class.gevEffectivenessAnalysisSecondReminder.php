<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevEffectivenessAnalysisSecondReminder extends gevCrsAutoMail {
	/**
	 * @var bool
	 */
	protected $send_to_training_officer = true;

	public function getTitle() {
		return "Reminder Superior";
	}
	
	public function _getDescription() {
		return "Second reminder for superiors to submit the effectiveness analysis. Will be send after 15 Days of first reminder. Interval every 2 Days";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "S02";
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