<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevAdminCancelBookedToCancelledWithBudgetCosts extends gevCrsAutoMail
{
	public function getTitle()
	{
		return "Info Teilnehmer";
	}

	public function _getDescription()
	{
		return "Teilnehmer (gebucht) erhält Buchungsstatus 'kostenpflichtig storniert' durch Stornierung durch Admin";
	}
	
	public function getScheduledFor()
	{
		return null;
	}

	public function getTemplateCategory()
	{
		return "C09";
	}

	public function getRecipientUserIDs()
	{
		return $this->getCourseCancelledWithBudgetCostsMembers();
	}

	public function getCC($a_recipient)
	{
		return $this->maybeSuperiorsCC($a_recipient);
	}

	public function getMail($a_recipient)
	{
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}

		return parent::getMail($a_recipient);
	}
}