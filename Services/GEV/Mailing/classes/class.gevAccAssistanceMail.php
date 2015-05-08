<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevCrsAutoMail
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once ("./Services/Mailing/classes/class.ilAutoMail.php");
require_once ("./Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once ("./Services/GEV/Utils/classes/class.gevUserUtils.php");

abstract class gevAccAssistanceMail extends ilAutoMail {

	protected $template_api;
	protected $template_settings;
	protected $template_variant;
	protected $mail_log;
	protected $global_bcc;
	protected $a_id;
	protected $a_usr;
	protected $ilias_url;
	protected $other;
	protected $def;
	protected $pwassistance_url=null;
	protected $logins=null;
	
	protected $acc_ass_mail_template_type;
	
	private static $template_type = "Account-assistance";
	
	public function __construct($a_id, $other) {
		global $ilDB, $lng, $ilCtrl, $ilias, $ilSetting;

		$this->a_id = $a_id;
		$this->other = $other;
		$this->a_usr = new ilObjUser($a_id);


		$this->db = &$ilDB;
		$this->lng = &$lng;
		$this->settings = &$ilSetting;
		$this->ilias = &$ilias;

		$this->template_api = null;
		$this->template_settings = null;
		$this->template_variant = null;
		$this->mail_log = null;
		$this->acc_ass_mail_template_type = self::$template_type;
		$this->global_bcc = null;

		parent::__construct($a_id);
	}

	// TODO: Move this to ilAutoMail

	public function getDescription() {
		return "Vorlage ".$this->getTemplateCategory() . ", " . $this->_getDescription();
	}

	abstract function _getDescription();

	// This will be evaluated by the mailing cron job only and could be
	// used to encode special circumstances under which the mail should not
	// be send (e.g. for reminders).
	// Per default disables sending of mails for offline courses.
	public function shouldBeSend() {
		return null;
	}

	public function getLastSend() {
		return null;
	}
	// SOME DEFAULTS

	public function getScheduledFor() {
		return null;
	}

	public function getUsersOnly() {
		return true;
	}

	public function getRecipientUserIDs() {
		return array($this->a_id);
	}

	public function getRecipientAddresses() {
		$ret = array(); 
		$ret[] = $this->a_usr->getEmail();
		return $ret;
	}

	protected function getAttachments() {

		return null;
	}

	public function getAttachmentPath($a_name) {
		return null;
	}

	protected function checkUserID($a_recipient) {
		return is_numeric($a_recipient) && ilObjUser::_lookupEmail($a_recipient) !== false;
	}


	private function initTemplateObjects($a_templ_id, $a_language) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateFrameSettingsEntity.php";
		
		if ($this->template_api === null) {
			$this->template_api = new ilMailTemplateManagementAPI();
		}
		if ($this->template_settings === null) {
			$this->template_settings = new ilMailTemplateSettingsEntity();
			$this->template_settings->setIlDB($this->db);
		}
		if($this->template_variant === null) {
			$this->template_variant = new ilMailTemplateVariantEntity();
			$this->template_variant->setIlDB($this->db);
		}
		if ($this->template_frame === null) {
			$this->template_frame = new ilMailTemplateFrameSettingsEntity($this->db, new ilSetting("mail_tpl"));
		}

		$this->template_settings->loadById($a_templ_id);
		$this->template_variant->loadByTypeAndLanguage($a_templ_id, $a_language);
	}

	protected function getTemplateIdByTypeAndCategory($a_type, $a_category) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";

		if ($this->template_settings === null) {
			$this->template_settings = new ilMailTemplateSettingsEntity();
			$this->template_settings->setIlDB($this->db);
		}

		$this->template_settings->loadByCategoryAndTemplate($a_category, $a_type);
		return $this->template_settings->getTemplateTypeId();
	}


	protected function getFrom() {
		$fn = $this->settings->get("mail_system_sender_name");
		$fm = $this->ilias->getSetting("mail_external_sender_noreply");

		return $fn." <".$fm.">";
	}


	protected function getTo($a_user_id) {
		$tn = ilObjUser::_lookupFullname($a_user_id);
		$tm = ilObjUser::_lookupEmail($a_user_id);

		return $tn." <".$tm.">";
	}

	protected function getCC($a_recipient) {

		return array();
	}

	protected function getBCC($a_recipient) {

		return array();
	}

	protected function getFullnameForTemplate($a_recipient) {
		return ilObjUser::_lookupFullname($a_recipient);
	}

	protected function getEmailForTemplate($a_recipient) {
		return ilObjUser::_lookupEmail($a_recipient);
	}

	protected function getAttachmentsForMail($a_recipient) {
		return array();
	}

	protected function getMessage($a_template_id, $a_recipient) {
		$message = $this->getMessageFromTemplate($a_template_id
												, $a_recipient
												, $this->getFullnameForTemplate($a_recipient)
												, $this->getEmailForTemplate($a_recipient));

		return array( "from" => $this->getFrom()
					, "to" => $this->getTo($a_recipient)
					, "cc" => array()
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
	}

	// Turn template to mail content. Returns
	// a dict containing fields "subject", "plain" and "html"
	protected function getMessageFromTemplate($a_templ_id, $a_user_id, $a_email, $a_name) {
		
		require_once("Services/User/classes/class.ilObjUser.php");
		$this->initTemplateObjects($a_templ_id, $this->a_usr->getLanguage());

		require_once "./Services/GEV/Mailing/classes/class.gevAccAssistanceMailData.php";

		$mail_data = new gevAccAssistanceMailData($this->a_id, $this->other);

		if ($a_user_id !== null) {
			$mail_data->setRecipient($a_user_id, $a_email, null);
			$mail_data->initUserData(gevUserUtils::getInstance($a_user_id));
		}

		$adapter = $this->template_settings->getAdapterClassInstance();
		
		$placeholders = $adapter->getPlaceholdersLocalized();
		return $this->template_api->getPopulatedVariantMessages($this->template_variant
															   , $placeholders
															   , $mail_data
															   , $this->a_usr->getLanguage());
	}

	public function send($a_recipients = null, $a_occasion = null) {
		return parent::send($a_recipients, $a_occasion);
	}

	public function sendDeferred($a_recipients = null, $a_occasion = null) {
		return null;
	}

	public function getMail($a_recipient) {
		if (!$this->checkUserID($a_recipient)) {
			throw new Exception("This mail will only work for ILIAS-Users.");
		}
		return $this->getMessage($this->getTemplateId(), $a_recipient);
	}

	public function getTemplateId() {
		return $this->getTemplateIdByTypeAndCategory($this->getTemplateType(), $this->getTemplateCategory());
	}

	public function getTemplateType() {
		return $this->acc_ass_mail_template_type;
	}

	abstract public function getTemplateCategory();

	protected $additional_mail_settings = null;

	protected function maybeSuperiorsCC($a_recipient) {
		return null;
	}

	protected function getDefaultOccasion() {
		return null;
	}
}
?>