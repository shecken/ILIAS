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
			//var_dump($mail);

			$recipient = $mail->getRecipient();
			$contexts = $mail->getContexts();

			$builder =  $this->content_builder->withData(
				$mail->getTemplateIdentifier(),
				$contexts
			);

			$subject = $builder->getSubject();
			$msg_html = $builder->getMessage();
			$msg_plain = $builder->getPlainMessage();
			$embedded = $builder->getEmbeddedImages();

			$mail_to_address = $recipient->getMailAddress();
			$mail_to_name = $recipient->getUserName();



			print '<hr>';
			$logcontext = $event;
			$usr_id = $recipient->getUserId();
			$mail_id = $builder->getTemplateId();
			$mail_id = $builder->getTemplateIdentifier();
			$crs_ref_id = null;
			foreach ($contexts as $context) {
				if(get_class($context) === 'ilTMSMailContextCourse') {
					$crs_ref_id = $context->getCourseRefId();
				}
			}

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

			$err = $this->sender->Send();
			if($err === false) {
			//	var_dump($this->sender->ErrorInfo);
			}

			print '<hr>';
			print(sprintf('From: %s (%s)', $mail_from_address, $mail_from_name));
			print '<br>';
			print(sprintf('To: %s (%s)', $mail_to_address, $mail_to_name));
			print '<br>';
			print $subject;
			print '<br>';
			print $msg_plain;
			print '<br>';
			print '<br>';
			print $msg_html;

			var_dump($this->sender->ErrorInfo);


			$this->logger->log(
				$logcontext,
				$usr_id,
				$mail_id,
				$crs_ref_id,
				$subject,
				$msg_plain
			);



		}
		die('in clerk');
	}
}