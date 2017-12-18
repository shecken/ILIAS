<?php

namespace CaT\IliasUserOrguImport\Log;

interface Log
{
	/**
	 * Get log entries corresponding to certain boundary conditions.
	 * I.e. ids of orgus and users involved in this entry.
	 *
	 * @param	string[string]	$entry_md
	 * @return	mixed[][]
	 */
	public function lookupEntries(array $entry_md);

	/**
	 * Create ne entry corresponding to given conditions.
	 *
	 * @param	string	$entry
	 * @param	string[string]	$entry_md
	 * @return	void
	 */
	public function createEntry($entry, array $entry_md);
}
