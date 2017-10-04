<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use CaT\Ente\Component;

namespace ILIAS\TMS\Booking;

/**
 * This is one step in the booking process of the user. It is provided as
 * an ente-component, since there will be multiple plugins participating
 * in the booking process. The order of the steps is determined via a priority.
 * Every step shows a form to the user and prompts the user for input. Once
 * the step is satisfied, the input of the user will be turned into a
 * serialisable form. This is then stored by the handler of this component
 * until all steps are finished. The step may show a short information for one
 * last confirmation based on the stored input. Afterwards the step needs
 * to process the stored input.
 */
interface Step {
	/**
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the step should be performed earlier.
	 *
	 * @return	int
	 */
	public function getPriority();

	/**
	 * Get the form to prompt the user.
	 *
	 * If $post is supplied, the form should be filled with the supplied values.
	 *
	 * @param	array	$post
	 * @return \ilPropertyFormGUI
	 */
	public function getForm(array $post = null);

	/**
	 * Get the data the step needs to store until the end of the process, based
	 * on the form.
	 *
	 * The data needs to be plain PHP data that can be serialized/unserialized
	 * via json.
	 *
	 * If null is returned, the form was not displayed correctly and needs to
	 *
	 * @return	mixed|null
	 */
	public function getData(\ilPropertyForm $form);

	/**
	 * Use the data to append a short summary of the step data to the form.
	 *
	 * The data must be the same as the component return via getData.
	 *
	 * @param	mixed		$data
	 * @param	\ilPropertyForm	$form
	 * @return	void
	 */
	public function appendOverview($data, \ilPropertyForm $form);

	/**
	 * Process the data to perform the actions in the system that are required
	 * for the step.
	 *
	 * The data must be the same as the component return via getData.
	 *
	 * @param	mixed $data
	 * @return	void
	 */
	public function	processStep($data);
}

