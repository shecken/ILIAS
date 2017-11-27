<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

class TMSMailClerk {

	/**
	 * @var MailContentBuilder
	 */
	protected $content_builder;


	public function __construct($content_builder, $logger) {
		$this->content_builder = $content_builder;
		$this->logger = $logger;
	}

	public function process($mails, $logcontext) {

		foreach ($mails as $mail) {
			//var_dump($mail);

			$recipient = $mail->getRecipient();
			$contexts = $mail->getContexts();

			$builder =  $this->content_builder->withData(
				$mail->getTemplateIdentifier(),
				$contexts
			);

			var_dump($recipient->getMailAddress());
			var_dump($builder->getSubject());
			var_dump($builder->getMessage());


			$logcontext = $logcontext;
			$usr_id = $recipient->getUserId();
			$mail_id = $builder->getTemplateId();
			$mail_id = $builder->getTemplateIdentifier();
			$subject = $builder->getSubject();
			$msg = $builder->getMessage();

			$crs_ref_id = null;
			foreach ($contexts as $context) {
				if(get_class($context) === 'ilTMSMailContextCourse') {
					$crs_ref_id = $context->getCourseRefId();
				}
			}

			$this->logger->log(
				$logcontext,
				$usr_id,
				$mail_id,
				$crs_ref_id,
				$subject,
				$msg
			);

		}
		die('in clerk');
	}
}