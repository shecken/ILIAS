<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */
use ILIAS\TMS\Mailing;
require_once 'Services/Mail/classes/class.ilMailTemplateDataProvider.php';
/**
 * This builds content for mails in TMS, as e.g. used for
 * automatic notifications in courses.
 *
 */
class ilTMSMailContentBuilder implements Mailing\MailContentBuilder {

	/**
	 * @var
	 */
	protected $mailing_db;
	/**
	 * @var
	 */
	protected $ident;
	/**
	 * @var
	 */
	protected $contexts;
	/**
	 * @var
	 */
	protected $template;

	//get all placeholder ids (w/o [])
	//read: lookahead for bracket, all chars, end with bracket
	const PLACEHOLDER = "/(?<=\[)[^]]+(?=\])/";

	public function __construct($mailing_db) {
		$this->mailing_db = $mailing_db;
	}

	public function withData($ident, $contexts) {
		$clone = clone $this;
		$clone->ident = $ident;
		$clone->contexts = $contexts;
		$clone->initTemplate();
		return $clone;
	}


	/**
	 *
	 */
	private function initTemplate() {
		$template_provider = new \ilMailTemplateDataProvider();
		list($template_id, $template_context) = $this->mailing_db->getTemplateIdAndContextByTitle($this->getTemplateIdentifier());
		$this->template_id = $template_id;
		$this->template = $template_provider->getTemplateById($template_id);
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateId() {
		return $this->template_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateIdentifier() {
		return $this->ident;
	}

	/**
	 * Get the subject of Mail with placeholders applied
	 *
	 * @return string
	 */
	public function getSubject(){
		return $this->template->getSubject();
	}

	/**
	 * @inheritdoc
	 */
	public function getMessage(){
		$body = $this->template->getMessage();
		$placeholders = array();

		preg_match_all(self::PLACEHOLDER, $body, $placeholders);
		var_dump($placeholders);
		foreach ($placeholders[0] as $placeholder) {
			$search = '[' .$placeholder .']';
			$value = '';
			foreach ($this->contexts as $context) {
				$v = $context->valueFor($placeholder);
				if($v) {
					$value = $v;
				}
			}
			$body = str_replace($search, $value, $body);
			var_dump($search);
			var_dump($value);
		}
		return $body;
	}





}