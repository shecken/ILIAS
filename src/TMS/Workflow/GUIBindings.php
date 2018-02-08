<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Workflow;

/**
 * Defines the bindings the player needs to the ILIAS-GUIs.
 */
interface GUIBindings {
	/**
	 * Get a form instance.
	 *
	 * @return \ilPropertyFormGUI
	 */
	public function getForm();

	/**
	 * Resolve a language identifier.
	 *
	 * @param	string	$id
	 * @return	string
	 */
	public function txt($id);

	/**
	 * Redirect to the previous location with some messages for the user.
	 *
	 * @param	string[] $messages
	 * @param	bool     $success
	 * @return	void
	 */
	public function redirectToPreviousLocation($messages, $success);
}
