<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

/**
 * Generali mail data for AccAssistance
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @version $Id$
 */

class gevAccAssistanceMailData extends ilMailData {

	protected $rec_user_id;
	protected $rec_email;
	protected $rec_fullname;
	protected $cache;
	protected $usr_utils;
	protected $other;



	public function __construct($usr_id, $other) {
		$this->rec_user_id = $usr_id;
		$this->other = $other;
	}
	
	function getRecipientMailAddress() {
		return $this->rec_email;
	}
	function getRecipientFullName() {
		return $this->rec_fullname;
	}
	
	function hasCarbonCopyRecipients() {
		return false;
	}
	
	function getCarbonCopyRecipients() {
		return array();
	}
	
	function hasBlindCarbonCopyRecipients() {
		return false;
	}
	
	function getBlindCarbonCopyRecipients() {
		return array();
	}
	
	function maybeFormatEmptyField($val) {
		if ($val === null) {
			return "-";
		}
		else {
			return $val;
		}
	}
	
	function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false) {
		$val = null;
		global $lng;
		
		switch ($a_placeholder_code) {
			
			case "EMAIL":
				$val = $this->rec_email;
				break;
			case "USR_LOGINS":
				if(!$a_markup) {
					$glue = "\n";
				} else {
					$glue = "<br>";
				}
				if($val = $this->other["LOGIN"]) {
					$val = implode($glue,$val);
				} 
				break;
			case "PWASSIST_LINK":
				$val = $this->other["PWASSIST_LINK"];
				if(!$val) {
					$val = null;
				}
				break;
			default:
				return $a_placeholder_code;
		}
		
		$val = $this->maybeFormatEmptyField($val);
		if (!$a_markup) {
			$val = strip_tags($val);
		}
		return $val;
	}

	// Phase 2: Attachments via Maildata
	function hasAttachments() {
		return false;
	}
	function getAttachments($a_lng) {
		return array();
	}
	
	function getRecipientUserId() {
		return $this->a_id;
	}
	
	function setRecipient($a_user_id, $a_email, $a_name) {
		$this->cache = array();
		$this->rec_user_id = $a_user_id;
		$this->rec_email = $a_email;
	}
	function initUserData(gevUserUtils $a_usr) {
		$this->cache = array();
		$this->usr_utils = $a_usr;
		$this->rec_email = $a_usr->getEmail();
	}
}

?>