<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS;


/**
 * This is to accumulate translations.
 */
class TranslationsImpl implements Translations  {

	/**
	 * @var Translations | null
	 */
	private $parent;

	/**
	 * @var array <string, string>
	 */
	private $translations = array();


	public function __construct(array $translations, Translations $parent = null) {
		$this->parent = $parent;

		$parent_trans = array();
		if(! is_null($this->parent)){
			$parent_trans = $this->parent->getTxts();
		}
		$this->translations = array_replace($parent_trans, $translations);
	}

	/**
	 * @inheritdoc
	 */
	public function getTxts() {
		return $this->translations;
	}

	/**
	 * @inheritdoc
	 */
	public function getTxt($id) {
		assert('is_string($id)');
		$txts = $this->getTxts();
		if(array_key_exists($id, $txts)) {
			return $txts[$id];
		} else {
			return '-' .$id .'-';
		}
	}
}
