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
	const DEFAULT_WRAPPER = './Services/Mail/templates/default/tpl.html_mail_template.html';
	const DEFAULT_IMAGES = './Services/Mail/templates/default/img/';
	const CUSTOM_WRAPPER = './Customizing/global/skin/custom/Services/Mail/tpl.html_mail_template.html';
	const CUSTOM_IMAGES = './Customizing/global/skin/custom/Services/Mail/img/';

	//get all placeholder ids (w/o [])
	//read: lookahead for bracket, all chars, end with bracket
	const PLACEHOLDER = "/(?<=\[)[^]]+(?=\])/";

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

	public function __construct($mailing_db) {
		$this->mailing_db = $mailing_db;
	}

	/**
	 * @inheritdoc
	 */
	public function withData($ident, $contexts) {
		$clone = clone $this;
		$clone->ident = $ident;
		$clone->contexts = $contexts;
		$clone->initTemplate();
		return $clone;
	}

	/**
	 *@return void
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
		return $this->resolvePlaceholders($this->template->getSubject());
	}

	/**
	 * @inheritdoc
	 */
	public function getMessage(){
		$msg = nl2br($this->getResolvedMessage());
		$wrapper = $this->getWrapper();
		$body = str_replace('{PLACEHOLDER}', $msg, $wrapper);
		return $body;
	}

	/**
	 * @inheritdoc
	 */
	public function getPlainMessage(){
		return strip_tags($this->getResolvedMessage());
	}

	/**
	 * @inheritdoc
	 */
	public function getEmbeddedImages(){
		$files = array();
		if(file_exists(self::CUSTOM_WRAPPER)){
			if(is_dir(self::CUSTOM_IMAGES)) {
				$files = $this->readDir(self::CUSTOM_IMAGES);
			}
		} else {
			$files = $this->readDir(self::DEFAULT_IMAGES);
		}
		return $files;
	}

	/**
	 * Replaces all placeholders.
	 *
	 * @return string
	 */
	private function getResolvedMessage(){
		return $this->resolvePlaceholders($this->template->getMessage());
	}

	/**
	 * Resolve all placeholder in txt
	 *
	 * @param string $txt
	 * @return string
	 */
	private function resolvePlaceholders($txt) {
		$placeholders = array();

		preg_match_all(self::PLACEHOLDER, $txt, $placeholders);
		foreach ($placeholders[0] as $placeholder) {
			$search = '[' .$placeholder .']';
			$value = '';
			foreach ($this->contexts as $context) {
				$v = $context->valueFor($placeholder);
				if($v) {
					$value = $v;
				}
			}
			$txt = str_replace($search, $value, $txt);
		}
		return $txt;

	}


	private function getWrapper() {
		if(!file_exists(self::CUSTOM_WRAPPER)) {
			$bracket = file_get_contents(self::DEFAULT_WRAPPER);
		} else {
			$bracket = file_get_contents(self::CUSTOM_WRAPPER);
		}
		return $bracket;
	}

	private function readDir($dirpath) {
		$files = array_diff(scandir($dirpath), array('.', '..'));
		$ret = array();
		foreach ($files as $file) {
			$ret[] = array($dirpath.$file, 'img/'.$file);
		}
		return $ret;
	}

}
