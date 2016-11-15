<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilMailingGUI.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

/**
* Class gevCrsMailingGUI
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevCrsMailingGUI extends ilMailingGUI {
	const TO_SUPERIOR = "to_superior";
	const TO_CC = "to_cc";

	protected function attachmentsSubtabVisible() {
		return true;
	}

	protected function autoMailsSubtabVisible() {
		return true;
	}

	protected function mailToMembersSubtabVisible() {
		return true;
	}

	protected function mailLogSubtabVisible() {
		return true;
	}

	protected function invitationMailTabVisible() {
		return true;
	}
	
	protected function additionalSettingsTabVisible() {
		return true;
	}

	protected function getMemberUserIds() {
		return $this->getCourse()->getMembersObject()->getParticipants();
	}

	protected function initMailAttachments() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
		$this->setMailAttachments(new gevCrsMailAttachments($this->obj_id));
	}

	protected function initAutoMails() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$this->setAutoMails(new gevCrsAutoMails($this->obj_id));
	}

	protected function initMailLog() {
		require_once("Services/Mailing/classes/class.ilMailLog.php");

		if ($this->mail_log === null) {
			$this->mail_log = new ilMailLog($this->obj_id);
		}
	}

	protected function initInvitationMailSettings() {
		$this->setInvitationMailSettings(new gevCrsInvitationMailSettings($this->obj_id));
	}

	protected function initAdditionalMailSettings() {
		$this->setAdditionalMailSettings(new gevCrsAdditionalMailSettings($this->obj_id));
	}

	protected $invitation_mail_settings;

	protected function setInvitationMailSettings(gevCrsInvitationMailSettings $a_settings) {
		$this->invitation_mail_settings = $a_settings;
	}

	protected function getInvitationMailSettings() {
		if ($this->invitation_mail_settings === null) {
			$this->initInvitationMailSettings();

			if ($this->invitation_mail_settings == null) {
				throw new Exception("Member invitation_mail_settings still ".
									"unitialized after call to initInvitationMailSettings. ".
									" Did you forget to call setInvitationMailSettings in ".
									"you implementation of initInvitationMailSettings?");
			}
		}

		return $this->invitation_mail_settings;
	}


	protected $additional_mail_settings;

	protected function setAdditionalMailSettings(gevCrsAdditionalMailSettings $a_settings) {
		$this->additional_mail_settings = $a_settings;
	}

	protected function getAdditionalMailSettings() {
		if ($this->additional_mail_settings === null) {
			$this->initAdditionalMailSettings();

			if ($this->additional_mail_settings == null) {
				throw new Exception("Member additional_mail_settings still ".
									"unitialized after call to initAdditionalMailSettings. ".
									" Did you forget to call setAdditionalMailSettings in ".
									"you implementation of initAdditionalMailSettings?");
			}
		}

		return $this->additional_mail_settings;
	}

	protected $crs;

	public function __construct($a_obj_id, $a_ref_id, $a_parent_gui) {
		parent::__construct($a_obj_id, $a_ref_id, $a_parent_gui);

		global $ilAccess, $ilUser;

		$this->access = &$ilAccess;
		$this->user = &$ilUser;

		$this->invitation_mail_settings = null;
		$this->crs = null;
		$this->crs_utils = null;
	}

	protected function getCourse() {
		if ($this->crs === null) {
			$this->crs = new ilObjCourse($this->obj_id, false);
		}

		return $this->crs;
	}
	
	protected function getCourseUtils() {
		if ($this->crs_utils === null) {
			$this->crs_utils = &gevCourseUtils::getInstance($this->obj_id);
		}
		
		return $this->crs_utils;
	}

	protected function executeCustomCommand($a_cmd) {
		switch($a_cmd) {
			case "showInvitationMails":
			case "updateInvitationMails":
			case "confirmRefreshAttachments":
			case "refreshAttachments":
			case "previewInvitationMail":
			
			case "showAdditionalSettings":
			case "updateAdditionalSettings":
			
				$this->$a_cmd();
				break;
			default:
				die("Unknown command: ".$a_cmd);
		}
	}

	protected function setSubTabs() {
		// add sub tab for invitation mails here
		if($this->invitationMailTabVisible()) {
			$this->tabs->addSubTab( "invitationMails"
								  , $this->lng->txt("gev_crs_settings_invitation")
								  , $this->ctrl->getLinkTarget($this, "showInvitationMails")
								  );
		}

		parent::setSubTabs();
		
		if ($this->additionalSettingsTabVisible()) {
			$this->tabs->addSubTab("additionalSettings"
								  , $this->lng->txt("gev_mailing_additional_settings")
								  , $this->ctrl->getLinkTarget($this, "showAdditionalSettings")
								  );
		}
	}

	protected function activateCustomSubTab($a_cmd) {
		switch($a_cmd) {
			case "showInvitationMails":
			case "updateInvitationMails":
			case "previewInvitationMail":
				$this->tabs->setSubTabActive("invitationMails");
				break;

			case "showAdditionalSettings":
			case "updateAdditionalSettings":
				$this->tabs->setSubTabActive("additionalSettings");
				break;

			case "confirmRefreshAttachments":
			case "refreshAttachments":
				$this->tabs->setSubTabActive("attachments");
				break;
			default:
				throw new Exception("Unknown command: ".$a_cmd);
		}
	}
	
	protected function showOverrideAttachmentConfirmation($a_file) {
		if ($this->getMailAttachments()->isAutogeneratedFile($a_file["name"])) {
			ilUtil::sendFailure(sprintf($this->lng->txt("gev_cant_replace_autogenerated_file"), $a_file));
			$this->showAttachments();
			return;
		}
		
		parent::showOverrideAttachmentConfirmation($a_file);
	}

	// INVITATION_MAILS

	protected function getFunctionsForInvitationMails() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		$roles = gevCourseUtils::getCustomRoles($this->obj_id);
		$ret = array($this->lng->txt("crs_member"), $this->lng->txt("crs_tutor"));
		
		foreach($roles as $role) {
			$ret[] = $role["title"];
		}

		return $ret;
	}

	protected function showInvitationMails() {
		$tpl = new ilTemplate("tpl.invitation_mail_settings.html", false, true, "Services/GEV/Mailing");

		$tpl->setVariable("INVITATION_MAIL_SETTINGS", $this->lng->txt("gev_inv_mail_settings"));
		$tpl->setVariable("INVITATION_MAIL_SETTINGS_NOTE", $this->lng->txt("gev_inv_mail_settings_note"));
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$tpl->setVariable("TXT_INVITATION_MAILS", $this->lng->txt("gev_crs_settings_invitation"));


		// Standardmail
		$mail_select = $this->getMailTemplateSelect("standard", $this->lng->txt("dont_send_mail"));
		$attachment_select = $this->getInvitationAttachmentSelect("standard");
		$tpl->setCurrentBlock("standard_mail");
		$tpl->setVariable("TXT_STANDARD_MAIL", $this->lng->txt("gev_standard_mail"));
		$tpl->setVariable("MAIL_SELECTION", $mail_select->render());
		$tpl->setVariable("ATTACHMENT_SELECTION", $attachment_select?$attachment_select->render():"");
		$this->ctrl->setParameter($this, "function", "standard");
		$tpl->setVariable("URL_PREVIEW", $this->ctrl->getLinkTarget($this, "previewInvitationMail"));
		$this->ctrl->clearParameters($this);
		$tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("mails_for_functions");
		$tpl->setVariable("TXT_FUNCTION", $this->lng->txt("gev_crs_function"));
		$tpl->setVariable("TXT_TEMPLATE", $this->lng->txt("gev_crs_mail_template"));
		$tpl->setVariable("TXT_ATTACHMENT", $this->lng->txt("mail_attachments"));

		// Mails for people with functions
		$functions = $this->getFunctionsForInvitationMails();

		$count = 0;

		foreach ($functions as $name) {
			$mail_select = $this->getMailTemplateSelect($name, $this->lng->txt("use_standard_mail"));
			$attachment_select = $this->getInvitationAttachmentSelect($name);

			$tpl->setCurrentBlock("row_bl");
			$tpl->setVariable("FUNCTION_NAME", $name);
			$tpl->setVariable("MAIL_SELECTION", $mail_select->render());
			if($attachment_select !== null AND $this->getInvitationMailSettings()->getTemplateFor($name) != -1) {
				$tpl->setVariable("ATTACHMENT_SELECTION", $attachment_select->render());
			}
			else {
				$tpl->setVariable("ATTACHMENT_SELECTION", "&nbsp;");
			}
			$this->ctrl->setParameter($this, "function", $name);
			$tpl->setVariable("URL_PREVIEW", $this->ctrl->getLinkTarget($this, "previewInvitationMail"));
			$this->ctrl->clearParameters($this);
			$tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
			$tpl->setVariable("ROW_CLASS", ($count % 2 == 0?"tblrow1":"tblrow2"));
			$tpl->parseCurrentBlock();

			$count++;
		}

		$tpl->parseCurrentBlock();

		$this->tpl->setContent($tpl->get());
	}

	protected function previewInvitationMail() {
		$function = $_GET["function"];

		if (!$function) {
			$this->ctrl->redirect($this, "showInvitationMails");
		}

		require_once("Services/Mailing/classes/class.ilMailViewGUI.php");

		$mail = $this->getAutoMails()->getInvitationMailFor($function, $this->user->getId());

		if ($mail === null) {
			ilUtil::sendFailure($this->lng->txt("no_invitation_mail_available"));
			return $this->showInvitationMails();
		}

		$is_html_mail = strlen($mail["message_html"]) > 0;

		$view_gui=  new ilMailViewGUI( $this->lng->txt("preview").": "."Einladungsmail für ".$this->lng->txt($function)
									 , $this->ctrl->getLinkTarget($this, "showInvitationMails")
									 , $mail["subject"]
									 , $is_html_mail ? $mail["message_html"] : $mail["message_plain"]
									 , $is_html_mail ? $mail["frame_html"] : $mail["frame_plain"]
									 , $is_html_mail ? $mail["image_path"] : null
									 , $is_html_mail ? $mail["image_style"] : null
									 , $mail["attachments"]
									 );

		$this->tpl->setContent($view_gui->getHTML());
	}

	protected function updateInvitationMails() {
		$functions = $this->getFunctionsForInvitationMails();
		$functions[] = "standard";

		$success = true;

		foreach ($functions as $name) {
			if (!array_key_exists($name, $_POST)) {
				die("Settings for ".$name." not found in POST-data.");
			}

			$settings = $_POST[$name];

			if (!array_key_exists("template", $settings)) {
				die("No template set for ".$name.".");
			}
			if (!array_key_exists("attachments", $settings)
				// template = -1 is the standard mail, no extra attachments here.
				OR ($name != "standard" AND $settings["template"] == -1)) {
				$settings["attachments"] = array();
			}

			$this->getInvitationMailSettings()->setSettingsFor($name, $settings["template"], $settings["attachments"]);
		}

		if ($success) {
			$this->getInvitationMailSettings()->save();
			ilUtil::sendSuccess($this->lng->txt("gev_invitation_mail_settings_success"));
		}

		$this->showInvitationMails();
	}

	protected function getMailTemplateSelect($a_function_name, $a_default_option) {
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");

		$select = new ilSelectInputGUI("", $a_function_name."[template]");
		$select->setOptions($this->getInvitationMailSettings()->getInvitationMailTemplates($a_default_option));
		$select->setValue($this->getInvitationMailSettings()->getTemplateFor($a_function_name));
		// TODO: Set current option
		return $select;
	}

	protected function getInvitationAttachmentSelect($a_function_name) {
		$select = $this->getAttachmentSelect();
		$select->setValue($this->getInvitationMailSettings()->getAttachmentNamesFor($a_function_name));
		$select->setTitle("");
		$select->setPostVar($a_function_name."[attachments]");
		$select->setHeight(75);
		$select->setWidth(160);
		return $select;
	}

	protected function showAttachmentRemoveFailure($a_filename) {
		$invMailFunctions = $this->getFunctionsForInvitationMails();
		$invMailFunctions[] = "standard";

		$functions = array();

		foreach($invMailFunctions as $function) {
			$att = $this->getInvitationMailSettings()->getAttachmentsFor($function);
			
			foreach($att as $attachment) {
				$this->ctrl->setParameter($this, "auto_mail_id", "participant_invitation");
				$this->ctrl->setParameter($this, "filename", $attachment["name"]);
				$link = $this->ctrl->getLinkTarget($this, "deliverAutoMailAttachment");
				$this->ctrl->clearParameters($this);
				$attachment["link"] = $link;
			}

			if (in_array($a_filename, $att)) {
				if ($function = "standard") {
					$functions[] = $this->lng->txt("gev_standard_mail");
				}
				else {
					$functions[] = sprintf($this->lng->txt("gev_invitation_mail_for"), $function);
				}
			}
		}

		ilUtil::sendFailure(sprintf($this->lng->txt("attachment_remove_failure"), $a_filename)." ".
							$this->lng->txt("gev_attachment_used_in").": <br />".
							implode($functions, "<br />"));
	}

	protected function showAttachments() {
		$res = parent::showAttachments();

		if ($this->getCourseUtils()->isTemplate()) {
			$this->toolbar->addSeparator();
			$this->toolbar->addFormButton($this->lng->txt("gev_refresh_attachments"), "confirmRefreshAttachments");
		}

		return $res;
	}

	// Refreshing of attachments at seminar templates.

	protected function confirmRefreshAttachments() {
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt("gev_confirm_refresh_attachments"));

		$conf->setConfirm($this->lng->txt("refresh"), "refreshAttachments");
		$conf->setCancel($this->lng->txt("cancel"), "showAttachments");

		require_once("./Services/GEV/Utils/classes/class.gevCourseUtils.php");

		foreach(gevCourseUtils::getInstance($this->obj_id)->getDerivedCourseIds() as $crs_id) {
			$util = gevCourseUtils::getInstance($crs_id);
			$conf->addItem("crs", $crs_id, $util->getTitle()." (".
							$util->getFormattedAppointment()
							.")");
		}

		$this->tpl->setContent($conf->getHTML());
	}

	protected function refreshAttachments() {
		require_once("./Services/GEV/Utils/classes/class.gevCourseUtils.php");
		foreach(gevCourseUtils::getInstance($this->obj_id)->getDerivedCourseIds() as $crs_id) {
			$this->getAttachments()->copyTo($crs_id);
		}

		ilUtil::sendSuccess($this->lng->txt("gev_attachments_refreshed"));
		return $this->showAttachments();
	}
	
	
	// ADDITIONAL SETTINGS
	
	protected function showAdditionalSettings($a_form = null) {
		if ($a_form === null) {
			$a_form = $this->getAdditionalSettingsForm();
		}

		$this->tpl->setContent($a_form->getHTML());
	}

	protected function updateAdditionalSettings() {
		$form = $this->getAdditionalSettingsForm();
		
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$this->getAdditionalMailSettings()->setSendListToAccomodation((bool) ($form->getInput("send_list_to_accom") == 1));
			$this->getAdditionalMailSettings()->setSendListToVenue((bool) ($form->getInput("send_list_to_venue") == 1));
			if (!$this->getAdditionalMailSettings()->getSuppressMails()) {
				$this->getAdditionalMailSettings()->setInvitationMailingDate(intval($form->getInput("inv_mailing_date")));
				$this->getAdditionalMailSettings()->setSuppressMails((bool) ($form->getInput("suppress_mails") == 1));
			}
			else {
				$form->getItemByPostVar("suppress_mails")->setChecked(true);
				$form->getItemByPostVar("inv_mailing_date")->setValue($this->getAdditionalMailSettings()->getInvitationMailingDate());
			}
			$this->getAdditionalMailSettings()->save();
			
			$form->getItemByPostVar("inv_mailing_date")->setDisabled($this->getAdditionalMailSettings()->getSuppressMails());
			$form->getItemByPostVar("suppress_mails")->setDisabled($this->getAdditionalMailSettings()->getSuppressMails());
			
			ilUtil::sendSuccess($this->lng->txt("gev_additional_settings_updated"));
			$this->getCourse()->update();
		}
		else {
			ilUtil::sendFailure($this->lng->txt("gev_additional_settings_update_failure"));
		}
		
		$this->showAdditionalSettings($form);
	}

	protected function getAdditionalSettingsForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNumberInputGUI.php");
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton("updateAdditionalSettings", $this->lng->txt("save"));
		
		$accom_mails = new ilFormSectionHeaderGUI();
		$accom_mails->setTitle($this->lng->txt("gev_memberlists"));
		$form->addItem($accom_mails);
		
		$send_list_to_accom = new ilCheckboxInputGUI();
		$send_list_to_accom->setTitle($this->lng->txt("gev_accomodation"));
		$send_list_to_accom->setPostvar("send_list_to_accom");
		$send_list_to_accom->setOptionTitle($this->lng->txt("auto_send_participant_list_accomodation"));
		$send_list_to_accom->setChecked($this->getAdditionalMailSettings()->getSendListToAccomodation());
		$form->addItem($send_list_to_accom);
		
		$send_list_to_venue = new ilCheckboxInputGUI();
		$send_list_to_venue->setTitle($this->lng->txt("gev_venue"));
		$send_list_to_venue->setPostvar("send_list_to_venue");
		$send_list_to_venue->setOptionTitle($this->lng->txt("auto_send_participant_list_venue"));
		$send_list_to_venue->setChecked($this->getAdditionalMailSettings()->getSendListToVenue());
		$form->addItem($send_list_to_venue);
		
		$mailing_dates = new ilFormSectionHeaderGUI();
		$mailing_dates->setTitle($this->lng->txt("gev_mailing_dates"));
		$form->addItem($mailing_dates);
		
		$inv_mailing_date = new ilNumberInputGUI();
		$inv_mailing_date->setTitle($this->lng->txt("gev_invitation_mail"));
		$inv_mailing_date->setPostvar("inv_mailing_date");
		$inv_mailing_date->setMinValue(0);
		$inv_mailing_date->setDecimals(0);
		$inv_mailing_date->setInfo($this->lng->txt("gev_mailing_inv_mailing_date_expl"));
		$inv_mailing_date->setValue($this->getAdditionalMailSettings()->getInvitationMailingDate());
		$inv_mailing_date->setDisabled($this->getAdditionalMailSettings()->getSuppressMails());
		$form->addItem($inv_mailing_date);
		
		$suppress_mails = new ilFormSectionHeaderGUI();
		$suppress_mails->setTitle($this->lng->txt("gev_mail_transport"));
		$form->addItem($suppress_mails);
		
		$suppress_mails = new ilCheckboxInputGUI();
		$suppress_mails->setTitle($this->lng->txt("gev_suppress_mails"));
		$suppress_mails->setPostvar("suppress_mails");
		$suppress_mails->setOptionTitle($this->lng->txt("gev_suppress_mails_info"));
		$suppress_mails->setChecked($this->getAdditionalMailSettings()->getSuppressMails());
		$suppress_mails->setDisabled($this->getAdditionalMailSettings()->getSuppressMails());
		
		$form->addItem($suppress_mails);
		
		return $form;
	}

	/**
	 * @inheritdoc
	 */
	protected function getMailToMembersForm($a_recipients) {
		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("./Services/Form/classes/class.ilTextInputGUI.php");
		require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		require_once("./Services/Form/classes/class.ilHiddenInputGUI.php");

		$user_data = $this->getUserData($a_recipients);

		$to = implode(", ", array_map(array($this, "userDataToString"), $user_data));
		$from = implode(", ", array_map( array($this, "userDataToString")
									   , $this->getUserData(array($this->user->getId()))
									   ));

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("mail_to_members"));

		$from_field = new ilNonEditableValueGUI($this->lng->txt("sender"), "from");
		$from_field->setValue($from);
		$form->addItem($from_field);

		$to_field = new ilNonEditableValueGUI($this->lng->txt("recipient"), "to");
		$to_field->setValue($to);
		$form->addItem($to_field);

		$to_superior = new ilCheckboxInputGUI($this->lng->txt("to_superior"), self::TO_SUPERIOR);
		$form->addItem($to_superior);

		$to_cc = new ilTextInputGUI($this->lng->txt("cc"), self::TO_CC);
		$form->addItem($to_cc);

		$about_field = new ilTextInputGUI($this->lng->txt("subject"), "subject");
		$form->addItem($about_field);

		$message_field = new ilTextAreaInputGUI($this->lng->txt("message"), "message");
		$message_field->setRows(10);
		$form->addItem($message_field);

		$attachment_select = $this->getAttachmentSelect();
		$form->addItem($attachment_select);

		foreach ($a_recipients as $recipient) {
			$recipient_hidden = new ilHiddenInputGUI("recipients[]");
			$recipient_hidden->setValue($recipient);
			$form->addItem($recipient_hidden);
		}

		$form->addCommandButton("sendMailToMembers", $this->lng->txt("send_mail"));

		return $form;
	}

	/**
	 * @inheritdoc
	 */
	protected function sendMailToMembers() {
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$recipients = $_POST["recipients"];
		if (!$recipients) {
			$this->selectMailToMembersRecipients();
			return;
		}

		$this->setCC($recipients, $_POST[self::TO_SUPERIOR], $_POST[self::TO_CC]);

		$form = $this->getMailToMembersForm($recipients);

		$form->setValuesByPost();

		if (!$form->checkInput()) {
			$this->tpl->setContent($form->getHTML());
			return;
		}

		$this->send( $this->user->getId()
				   , $form->getInput("recipients")
				   , $form->getInput("subject")
				   , $form->getInput("message")
				   , $form->getInput("attachments")
				   );

		ilUtil::sendSuccess($this->lng->txt("mail_to_members_send_successfully"));
		$this->showLog();
	}

	/**
	 * @inheritdoc
	 **/
	protected function send($a_from, $a_to, $a_subject, $a_message, $a_attachments) {
		require_once("./Services/Mail/classes/class.ilMimeMail.php");

		$attachments = array_map(array($this, "mapAttachmentToRecord"), $a_attachments);

		// gev-patch start
		global $ilias, $ilSetting;
		$fn = $ilSetting->get("mail_system_sender_name");
		$fm = $ilias->getSetting("mail_external_sender_noreply");
		// gev-patch end

		foreach($a_to as $recipient) {
			var_dump($this->mapUserIdToMailString($recipient));
			$mail_data = array(
						// gev-patch start
						//"from" => $this->mapUserIdToMailString($a_from)
						   "from" => $fn." <".$fm.">"
						// gev-patch end
						 , "to" => $this->mapUserIdToMailString($recipient)
						 , "cc" => $this->getCC($recipient)
						 , "bcc" => array()
						 , "subject" => $a_subject
						 , "message_plain" => $a_message
						 , "attachments" => $attachments
						 );

			$mail = new ilMimeMail();
			$mail->From($mail_data["from"]);
			$mail->To($mail_data["to"]);
			$mail->Cc($mail_data["cc"]);
			$mail->Bcc($mail_data["bcc"]);
			$mail->Subject($mail_data["subject"]);
			$mail->Body($mail_data["message_plain"]);
			foreach ($mail_data["attachments"] as $attachment) {
				$mail->Attach($attachment["path"]);
			}

			$mail->Send();
			$this->log($mail_data, $this->lng->txt("send_by").": ".ilObjUser::_lookupFullname($a_from));
		}
	}

	protected function getCC($recipient) {
		return $this->cc[$recipient];
	}

	protected function setCC($recipients, $to_superior, $to_cc) {
		$to_ccs = array();

		if($to_cc) {
			$to_cc = preg_replace('/,|;| /', '|', $to_cc);
			$to_ccs = array_filter(explode("|", $to_cc));
		}

		foreach ($recipients as $recipient) {
			$to_superios = array();
			if((bool)$to_superior) {
				$usr_utils = gevUserUtils::getInstance($recipient);
				foreach ($usr_utils->getDirectSuperiors() as $superior_id) {
					$to_superios[] = $this->mapUserIdToMailString($superior_id);
				}
			}

			$this->cc[$recipient] = array_merge($to_superios, $to_ccs);
		}
	}
}
