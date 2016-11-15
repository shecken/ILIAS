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
		$training_officer_name = $this->getTrainingOfficerName();
		$training_officer_mail = $this->getTrainingOfficerMail();

		if($training_officer_mail) {
			if(!$training_officer_name) {
				$training_officer_name = $training_officer_mail;
			}

			return array($training_officer_name." <".$training_officer_mail.">");
		}
		return array();
	}

	protected function log($a_mail, $a_occasion) {
	}

	protected function getMessage($a_template_id, $a_recipient) {
		$message = $this->getMessageFromTemplate($a_template_id
												, $a_recipient
												, $this->getFullnameForTemplate($a_recipient)
												, $this->getEmailForTemplate($a_recipient));

		$mail = array( "from" => $this->getFrom()
					, "to" => $this->getTo($a_recipient)
					, "cc" => $this->getCC($a_recipient)
					, "bcc" => $this->getBCC($a_recipient)
					, "subject" => $message["subject"]?$message["subject"]:""
					, "message_plain" => str_replace("<br />", "\n", $message["plain"])
					, "message_html" => $message["html"]
					, "attachments" => $this->getAttachmentsForMail($a_recipient)
					, "frame_plain" => $this->template_frame->getPlainTextFrame()
					, "frame_html" => $this->template_frame->getHtmlFrame()
					, "image_path" => $this->template_frame->getFileSystemBasePath()."/"
									  .$this->template_frame->getImageName()
					, "image_styles" => $this->template_frame->getImageStyles()
					);

		if(!$this->send_to_training_officer) {
			$mail["bcc"] = array();
		} else {
			$this->send_to_training_officer = false;
		}

		return $mail;
	}
}

?>