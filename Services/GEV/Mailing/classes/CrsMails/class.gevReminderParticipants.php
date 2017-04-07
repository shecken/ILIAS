<?php

require_once("Services/GEV/Mailing/classes/CrsMails/class.gevInvitation.php");

class gevReminderParticipants extends gevInvitation {
	const DAYS_BEFORE_COURSE_START = 5;

	public function getTitle() {
		return "Reminder Participant (Invitationmail)";
	}

	public function getDescription() {
		// Mail is send after the fourth day before training is over.
		// Thus we need to subtract, since after the fourth day is on the
		// third day.
		return (self::DAYS_BEFORE_COURSE_START -1)." Days before Begin of Training";
	}

	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date) {
			$date->increment(IL_CAL_DAY, -1 * self::DAYS_BEFORE_COURSE_START);
		}
		return $date;
	}

	public function shouldBeSend() {

		$today_str = (new DateTime('today'))->format('Y-m-d');
		$today = new ilDate($today_str,IL_CAL_DATE);
		$scheduled_for = $this->getScheduledFor();
		$crs_start = $this->getCourseUtils()->getStartDate();
		if(!$scheduled_for) {
			return false;
		}
		if(!ilDate::_within($today,$scheduled_for,$crs_start)) {
			return false;
		}
		return parent::shouldBeSend();
	}

	public function getMail($a_recipient) {
		if (!$this->checkUserID($a_recipient)) {
			throw new Exception("GEV-Invitation-Mails will only work for ILIAS-Users.");
		}
		
		if ($this->getCourseUtils()->isTemplate()) {
			return null;
		}
		
		if ($this->isForOnlineTraining()) {
			return null;
		}
		
		$function = $this->getUserFunction($a_recipient);

		// function will be null if user is not member of the course. Fall back to
		// standard mail.
		if ($function === null) {
			$function = "standard";
		}

		$mail = $this->getMailFor($function, $a_recipient);
		if ($mail !== null) {
			$mail["subject"] = "Reminder: ".$mail["subject"];
		}
		return $mail;
	}
}

/*class gevReminderParticipants extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 3;
	
	public function getTitle() {
		return "Erinnerung Teilnehmer";
	}
	
	public function _getDescription() {
		return self::DAYS_BEFORE_COURSE_START." Tage vor Trainingsbeginn";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date) {
			$date->increment(IL_CAL_DAY, -1 * self::DAYS_BEFORE_COURSE_START);
		}
		return $date;
	}
	
	public function getTemplateCategory() {
		return "R02";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}*/

?>