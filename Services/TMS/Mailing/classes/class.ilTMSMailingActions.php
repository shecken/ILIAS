<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Mailing;

require_once("Services/Mail/classes/class.ilMailFormCall.php");
require_once("Services/Mail/classes/class.ilFormatMail.php");
/* Copyright (c) 2017 Stefan Hecken <stefan.Hecken@concepts-and-training.de> */

/**
 * This is how ILIAS sends mails
 */
class ilTMSMailingActions implements Mailing\Actions {
	public function __construct(ilFormatMail $umail, Mailing\MailingDB $db) {
		$this->umail = $umail;
		$this->db = $db;
	}
	/**
	 * Sends a mail for a course to the user.
	 */
	public function sendCourseMail($mail_id, $crs_ref_id, $user_id) {
		list($template_id, $context) = $this->getTemplateIdAndContextByTitle($mail_id);
		list($subject, $message) = $this->getTemplateInformations($template_id);
		require_once("Services/User/classes/class.ilObjUser.php");
		ilMailFormCall::setContextParameters(array("ref_id" => $crs_ref_id, ilMailFormCall::CONTEXT_KEY => $context));
		$to = ilObjUser::_lookupLogin($user_id);

		if($errors = $this->umail->sendMail(
				$to, // to
				"", // cc
				"", // bcc
				$subject,
				$message,
				array(), //attachment
				array("normal"),
				true // use placeholder
			)
		) {
			// ToDo Irgendwas mit den Fehlern machen. Die werden ja leider nicht geworfen
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateIdAndContextByTitle($title) {
		return $this->db->getTemplateIdAndContextByTitle($title);
	}

	/**
	 * Get information of mailtemplate
	 *
	 * @param int 	$template_id
	 *
	 * @return string[]
	 */
	protected function getTemplateInformations($template_id) {
		require_once 'Services/Mail/classes/class.ilMailTemplateService.php';
		require_once 'Services/Mail/classes/class.ilMailTemplateDataProvider.php';
		$template_provider = new ilMailTemplateDataProvider();
		$template = $template_provider->getTemplateById($template_id);
		$context = ilMailTemplateService::getTemplateContextById($template->getContext());
		return array($template->getSubject(), $template->getMessage());
	}
}