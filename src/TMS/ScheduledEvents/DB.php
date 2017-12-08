<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\ScheduledEvents;

/**
 * Some actions in TMS must be carried out at a certain point in time,
 * e.g. the cancellation of a waitinglist or the sending of a reminder-mail
 * for a course.
 * In order to do that without querying the complete DB on every run of the cron,
 * Events are queued in the schedule.
 * A cron-job can then ask for due events and raise them.
 * In order to do that, a component providing such events must register them
 * here. The component is also responsible for keeping scheduled events up to date,
 * i.e. when a course-date changes, the component must notice and update the
 * schedule accordingly.
 * Finally, "only" an event is being raised when an event is due.
 * The Component (or any other listener) will carry out the action.
 */

interface ScheduleDB {

	/**
	 * Create a new scheduled event.
	 *
	 * @param DateTime 	$due
	 * @param string 	$component 	e.g. "Modules/Course"
	 * @param string 	$event 		e.g. "reached_end_of_booking_period"
	 * @param array<string,mixed> 	e.g. ['crs_ref_id' => 123, 'discard_waiting' => true]
	 *
	 * @return \ILIAS\TMS\ScheduledEvents\Event
	 */
	public function create($due, $component, $event, $params = array());

	/**
	 * Updates a scheduled event.
	 *
	 * @param \ILIAS\TMS\ScheduledEvents\Event $event
	 *
	 * @return void
	 */
	public function update(Event $event);

	/**
	 * Get all events.
	 *
	 * @return \ILIAS\TMS\ScheduledEvents\Event[]
	 */
	public function getAll();

	/**
	 * Get all events with dates before now.
	 *
	 * @return \ILIAS\TMS\ScheduledEvents\Event[]
	 */
	public function getAllDue();

	/**
	 * Declare these events as accouted for (i.e.:they were raised)
	 * Most likely: delete them from DB.
	 *
	 * @param \ILIAS\TMS\ScheduledEvents\Event[] $events
	 * @return void
	 */
	public function setAccountedFor($events);

}