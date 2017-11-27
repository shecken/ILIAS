<?php
namespace ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * everything a mail needs to know
 */
class TMSMail implements Mail {

	/**
	 * @var int
	 */
	protected $usr_id;

	/**
	 * @var string
	 */
	protected $template_ident;

	/**
	 * @var MailContexts[]
	 */
	protected $contexts;


	public function __construct($recipient, $template_ident, $contexts) {

		$this->recipient = $recipient;
		$this->template_ident = $template_ident;
		$this->contexts = $contexts;
	}

	/**
	 * @inheritdoc
	 */
	public function getRecipient() {
		return $this->recipient;
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateIdentifier() {
		return $this->template_ident;
	}

	/**
	 * @inheritdoc
	 */
	public function getContexts() {
		return $this->contexts;
	}
}
