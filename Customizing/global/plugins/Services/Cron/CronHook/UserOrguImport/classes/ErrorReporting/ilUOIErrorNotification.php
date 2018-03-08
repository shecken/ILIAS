<?php

use CaT\IliasUserOrguImport\ErrorReporting\ErrorCollection as ErrorCollection;

require_once 'Services/Mail/classes/class.ilMailNotification.php';

class ilUOIErrorNotification extends ilMailNotification
{

	public function __construct($rbac_review)
	{
		$this->rbac_review = $rbac_review;
	}

	public function notifyAboutErrors(ErrorCollection $errors)
	{
		if (!$errors->containsErrors()) {
			return;
		}
		foreach ($this->getAdministrators() as $admin_id) {
			$this->initLanguage($admin_id);
			$this->initMail();
			$subject = 'Notifications about errors during import';
			$this->setSubject($subject);
			$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
			$this->appendBody("\n\n");
			foreach ($errors as $error_text) {
				$this->appendBody("\t".$error_text);
				$this->appendBody("\n\n");
			}
			$this->appendBody($this->createPermanentLink());
			$this->getMail()->appendInstallationSignature(true);
			$this->sendMail([$admin_id], ['system']);
		}
	}

	protected function getAdministrators()
	{
		return $this->rbac_review->assignedUsers(SYSTEM_ROLE_ID);
	}
}
