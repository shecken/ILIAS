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
	 * @var array <string, mixed>
	 */
	private $txts = array();


	public function __construct(array $txts, Translations $parent = null) {
		$this->parent = $parent;
		$this->txts = $txts;
	}

	/**
	 * @inheritdoc
	 */
	public function getTxt($id) {
		assert('is_string($id)');
		if(array_key_exists($id, $this->txts)) {
			return $this->txts[$id];
		} elseif (! is_null($this->parent)) {
			return $this->parent->getTxt($id);
		}
		return '-' .$id .'-';
	}
}
