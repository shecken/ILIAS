<?php
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * Course- and Plugin-related placeholder-values
 * Subclass and provide this at plugins.
 */
class ilTMSMailContextEnte implements Mailing\MailContext {
	use ilHandlerObjectHelper;

	protected $entity;
	protected $owner;


	public function __construct($entity, $owner) {
		$this->entity = $entity;
		$this->owner = $owner;
		$this->txt = $txt;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return self::$PLACEHOLDERS;
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id) {
		return 'RESOLVED' . $placeholder_id;
	}


	/**
	 * @inheritdoc
	 */
	public function entity() {
		return $this->entity;
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntityRefId() {
		return $this->entity()->getRefId();
	}

	/**
	 * @inheritdoc
	 */
	protected function getDIC() {
		global $DIC;
		return $DIC;
	}

}
