<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

use CaT\Ente\Component;

/**
 * This is a component-interface for automails send in context of a course.
 */
interface CourseAutoMail extends Component {

	/**
	 * Does the instance provide mails for this event?
	 *
	 * @param string 	$event
	 * @return bool
	 */
	public function providesMailFor($event);

	/**
	 * Get the mail template's id to be used.
	 *
	 * @param string 	$event
	 * @param array[<string,mixed> 	$parameter
	 * @return int
	 */
	public function getMailTemplateId($event, $parameter);

	/**
	 * Get (additional) contexts needed for placeholder-replacements.
	 *
	 * @param string 	$event
	 * @param array[<string,mixed> 	$parameter
	 * @return MailContext[]
	 */
	public function getContexts($event, $parameter);


}

