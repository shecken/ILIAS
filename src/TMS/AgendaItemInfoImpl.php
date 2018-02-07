<?php
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * This is an information about a agenda item.
 */
class AgendaItemInfoImpl implements AgendaItemInfo {
	/**
	 * @var	Ente\Entity
	 */
	protected $entity;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string[]
	 */
	protected $topics;

	/**
	 * @var int 	$id
	 * @var string 	$title
	 * @var string[] $topics
	 */
	public function __construct(Ente\Entity $entity, $id, $title, array $topics) {
		assert('is_int($id)');
		assert('is_string($title)');

		$this->entity = $entity;
		$this->id = $id;
		$this->title = $title;
		$this->topics = $topics;
	}

	/**
	 * @inheritdocs
	 */
	public function entity() {
		return $this->entity;
	}

	/**
	 * @inheritdocs
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @inheritdocs
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @inheritdocs
	 */
	public function getTopics() {
		return $this->topics;
	}
}
