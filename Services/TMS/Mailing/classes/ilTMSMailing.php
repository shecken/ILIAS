<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */
use ILIAS\TMS\Mailing;

class ilTMSMailing implements Mailing\Actions {


	protected $mailing_db;
	protected $logging_db;

	private static function getDIC() {
		global $DIC;
		return $DIC;
	}

	/**
	 * @return MailingDB
	 */
	private function getMailingDB() {
		if(! $this->mailing_db) {
			require_once("Services/TMS/Mailing/classes/class.ilTMSMailingDB.php");
			$db = self::getDIC()->database();
			$this->mailing_db = new ilTMSMailingDB($db);
		}
		return $this->mailing_db;
	}

	/**
	 * @inheritdoc
	 */
	public function getMailLogDB() {
		if(! $this->logging_db) {
			require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
			$db = self::getDIC()->database();
			$this->logging_db = new ilTMSMailingLogsDB($db);
		}
		return $this->logging_db;
	}

	/**
	 * @inheritdoc
	 */
	public function getContentBuilder() {
		require_once("Services/TMS/Mailing/classes/class.ilTMSMailContentBuilder.php");
		return new \ilTMSMailContentBuilder($this->getMailingDB());

	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateDataByIdent($ident) {
		return $this->getMailingDB()->getTemplateDataByTitle($ident);
	}

	/**
	 * @inheritdoc
	 */
	public function getStandardSender() {
		require_once('./Services/TMS/Mailing/classes/class.ilTMSMailRecipient.php');
		require_once './Services/Mail/classes/class.ilMail.php';
		list($sender_mail, $sender_name) =  \ilMail::getIliasMailerAddress();
		$from = new \ilTMSMailRecipient();
		$from = $from
			->withName($sender_name)
			->withMail($sender_mail);
		return $from;
	}

	/**
	 * @inheritdoc
	 */
	public function getClerk() {
		$clerk = new Mailing\TMSMailClerk(
			$this->getContentBuilder(),
			$this->getMailLogDB(),
			$this->getStandardSender()
		);
		return $clerk;
	}

}