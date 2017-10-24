<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Mailing;

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
		$template_id = $this->getTemplateIdByTitle($mail_id);
		list($subject, $message) = $this->getTemplateInformations($template_id);
		require_once("Services/User/classes/class.ilObjUser.php");
		$to = ilObjUser::_lookupEmail($user_id);

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
	 * Get template id by template title
	 *
	 * @param string 	$title
	 *
	 * @return int
	 */
	protected function getTemplateIdByTitle($title) {
		return $this->db->getTemplateIdByTitle($title);
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