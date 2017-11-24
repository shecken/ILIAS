<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * Course-related placeholder-values
 */
class ilTMSMailContextCourse implements Mailing\MailContext {

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	public function __contruct($crs_ref_id) {
		assert('is_int($crs_ref_id)');
		$this->crs_ref_id = $crs_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id) {
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array();
	}
}
