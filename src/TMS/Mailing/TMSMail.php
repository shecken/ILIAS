<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * everything a mail needs to know
 */
class TMSMail implements Mail {

	/**
	 * @var Recipient
	 */
	protected $recipient;

	/**
	 * @var string
	 */
	protected $template_ident;

	/**
	 * @var MailContext[]
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
