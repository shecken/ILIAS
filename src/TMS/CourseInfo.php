<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * This is an information about a course, noteworthy for a user in some context.
 */
interface CourseInfo extends Component {
	const CONTEXT_SEARCH_SHORT_INFO = 1;
	const CONTEXT_SEARCH_DETAIL_INFO = 2;

	/**
	 * Get a label for this step in the process.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get the value of this field.
	 *
	 * @return	string
	 */
	public function getValue();

	/**
	 * Get a description for this step in the process.
	 *
	 * @return	string
	 */
	public function getDescription();

	/**
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the step should be performed earlier.
	 *
	 * @return	int
	 */
	public function getPriority();

	/**
	 * Check if the info is relevant in the given context.
	 *
	 * @param	mixed	$context from the list of contexts in this class
	 * @return	bool
	 */
	public function hasContext($context);
}

