<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;
use CaT\Ente\Component;

/**
 * This is a component-interface for automails.
 */
interface MailingOccasion extends Component {

	/**
	 * Does the instance provide mails for this event?
	 *
	 * @param string 	$event
	 * @return bool
	 */
	public function providesMailForEvent($event);

	/**
	 * get mails for this occasion
	 *
	 * @param string 	$event
	 * @param array<string, mixed> 	$parameter
	 * @return Mail[]
	 */
	public function getMails($event, $parameter);

}
