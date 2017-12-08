<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\ScheduledEvents;

/**
 * An event is raised according to the schedule.
 * The Event holds the necessary data.
 */
class Event {
	/**
	 * @var DateTime
	 */
	protected $due;

	/**
	 * @var string
	 */
	protected $component;

	/**
	 * @var string
	 */
	protected $event;

	/**
	 * @var array<string,mixed>
	 */
	protected $params;

	/**
	 * @param DateTime 	$due
	 * @param string 	$component 	e.g. "Modules/Course"
	 * @param string 	$event 		e.g. "reached_end_of_booking_period"
	 * @param array<string,mixed> 	e.g. ['crs_ref_id' => 123, 'discard_waiting' => true]
	 */
	public function __construct($due, $component, $event, $params = array()) {
		assert('is_a($due, "DateTime")');
		assert('is_string($component)');
		assert('is_string($event)');
		$this->due = $due;
		$this->component = $component;
		$this->event = $event;
		$this->params = $params;
	}

	/*
	 * @return DateTime
	 */
	public function getDue() {
		return $this->due;
	}

	/*
	 * @return string
	 */
	public function getComponent() {
		return $this->component;
	}

	/*
	 * @return string
	 */
	public function getEvent() {
		return $this->event;
	}

	/*
	 * @return array<string, mixed>
	 */
	public function getParameters() {
		return $this->params;
	}



}