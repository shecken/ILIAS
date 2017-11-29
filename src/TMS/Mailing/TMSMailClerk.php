<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

class TMSMailClerk {

	/**
	 * @var MailContentBuilder
	 */
	protected $content_builder;

	/**
	 * @var LoggingDB
	 */
	protected $logger;

	/**
	 * @var PHPMail
	 */
	protected $sender;

	/**
	 * @var Recipient
	 */
	protected $from;


	public function __construct($content_builder, $logger, Recipient $from) {
		$this->content_builder = $content_builder;
		$this->logger = $logger;
		$this->from = $from;
		$this->initSender();
	}

	private function initSender() {
		$this->sender = new \PHPMailer();
		$this->sender->CharSet = "utf-8";
		$this->sender->isHTML(true);
	}

	public function process($mails, $event) {

		$mail_from_address = $this->from->getMailAddress();
		$mail_from_name = $this->from->getUserName();

		foreach ($mails as $mail) {
			$recipient = $mail->getRecipient();
			$contexts = $mail->getContexts();
			$template_ident = $mail->getTemplateIdentifier();

			$builder =  $this->content_builder->withData($template_ident, $contexts);

			$subject = $builder->getSubject();
			$msg_html = $builder->getMessage();
			$msg_plain = $builder->getPlainMessage();
			$embedded = $builder->getEmbeddedImages();

			$mail_to_address = $recipient->getMailAddress();
			$mail_to_name = $recipient->getUserName();

$mail_to_address = 'nhaagen@cat06.de';

			$this->sender->setFrom($mail_from_address, $mail_from_name);
			$this->sender->addAddress($mail_to_address, $mail_to_name);
			$this->sender->Subject = $subject;
			$this->sender->Body = $msg_html;
			$this->sender->AltBody = $msg_plain;
			foreach ($embedded as $embed) {
				list($path, $file) = $embed;
				$this->sender->AddEmbeddedImage($path, $file);

			}

			$err = '';
			if(! $this->sender->Send()) {
				$err = $this->sender->ErrorInfo;
			};

			$mail_to_usr_id = $recipient->getUserId();
			$mail_to_usr_login = $recipient->getUserLogin();

			$crs_ref_id = null;
			foreach ($contexts as $context) {
				if(get_class($context) === 'ilTMSMailContextCourse') {
					$crs_ref_id = $context->getCourseRefId();
				}
			}

			$this->logger->log(
				$event,
				$template_ident,
				$mail_to_address,
				$mail_to_name,
				$mail_to_usr_id,
				$mail_to_usr_login,
				$crs_ref_id,
				$subject,
				$msg_plain,
				$err
			);
		}
		die('in clerk');
	}
}