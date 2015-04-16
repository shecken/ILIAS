<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class gevAccAssistanceMails
*
* @author Richard Klees <richard.klees@concepts-and-training>
* @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
*/

class gevAccAssistanceMails extends ilAutoMails {
	public function __construct($a_obj_id) {
		$this->mail_data = array(
		  "pw_assistance" 		=> "gevPWAssistMail"
		, "user_login_assist" 	=> "gevUserLoginAssistMail"
		);

		parent::__construct($a_obj_id);

		global $lng;
		$this->lng = &$lng;
	
		$this->lng->loadLanguageModule("mailing");
	}

	public function getTitle() {
		return "Automatische Mails";
	}

	public function getSubtitle() {
		return "";
	}

	public function getIds() {
		return array_keys($this->mail_data);
	}

	protected function createAutoMail($a_id) {
		if (!array_key_exists($a_id, $this->mail_data)) {
			throw new Exception("Unknown AutoMailID: ".$a_id);
		}
		
		require_once("./Services/GEV/Mailing/classes/AccountAssistance/class.".$this->mail_data[$a_id].".php");
		return new $this->mail_data[$a_id]($this->obj_id, $a_id);
	}
	
	public function sendDeferred($a_mail_id, $a_recipients = null, $a_occasion = null) {
		return $this->getAutoMail($a_mail_id)->sendDeferred($a_recipients, $a_occasion);
	}

	public function getUserOccasion() {
		return $this->lng->txt("send_by").": ".parent::getUserOccasion();
	}

	
	protected function initMailLog() {
		$this->setMailLog(new ilMailLog($this->obj_id));
	}
}