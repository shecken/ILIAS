<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Workflow;

/**
 * Stores state information about workflows.
 */
interface StateDB {
	/**
	 * @param	string	$workflow_id
	 * @return	State|null
	 */
	public function load($workflow_id);

	/**
	 * @param	State
	 * @return	void
	 */
	public function save(State $state);

	/**
	 * Deletes ProcessState if it exists.
	 *
	 * @param	State
	 * @return	void
	 */
	public function delete(State $state);
}
