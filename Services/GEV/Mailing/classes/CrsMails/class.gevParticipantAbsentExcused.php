<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantAbsentExcused extends gevCrsAutoMail
{
	public function getTitle()
	{
		return "Info Teilnehmer";
	}

	public function _getDescription()
	{
		return "Teilnehmer erhält Teilnahmestatus 'fehlt entschuldigt'";
	}

	public function getScheduledFor()
	{
		return null;
	}

	public function getTemplateCategory()
	{
		return "F02";
	}

	public function getRecipientUserIDs()
	{
		return $this->getCourseExcusedParticipants();
	}

	public function getCC($a_recipient)
	{
		return array();
	}

	public function getMail($a_recipient)
	{
		require_once("Services/GEV/Utils/classes/class.gevExpressLoginUtils.php");
		$exprUserUtils = gevExpressLoginUtils::getInstance();

		if ($exprUserUtils->isExpressUser($a_recipient)) {
			return null;
		}

		return parent::getMail($a_recipient);
	}

	public function shouldBeSend()
	{
		include_once 'Modules/Course/classes/class.ilObjCourseAccess.php';
		if (ilObjCourseAccess::_isOffline($this->crs_id)) {
			return false;
		}

		if ($this->getCourseIsCoaching()) {
			return false;
		}

		return true;
	}
}
