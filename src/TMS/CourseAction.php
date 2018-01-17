<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
interface CourseAction extends Component {

	/**
	 * Checks the action is allowed for user
	 *
	 * @param int 	$usr_id 	Id of user the action is requested for
	 *
	 * @return bool
	 */
	public function isAllowedFor($usr_id);

	/**
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the action will be displayed in later position
	 *
	 * @return int
	 */
	public function getPriority();

	/**
	 * Get the ui control to render the action
	 *
	 * @return UIControl
	 */
	public function getControlItem();

	/**
	 * Check if the info is relevant in the given context.
	 *
	 * @param mixed 	$context from the list of contexts in this class
	 *
	 * @return bool
	 */
	public function hasContext($context);
}