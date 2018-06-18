<?php

/**
 * Wrapper arround php file operations.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class gevADPFile
{
	/**
	 * Open a file and return a file handle.
	 *
	 * @param 	string 	$raw_path
	 * @return 	resource
	 */
	public function open($raw_path)
	{
		assert('is_string($raw_path)');

		return fopen($raw_path, 'r');
	}

	/**
	 * Read a line from a csv-file-handle.
	 *
	 * @param 	resource 	$handle
	 * @param 	string 		$delimeter
	 * @return 	array
	 */
	public function readCSVLine($handle, $delimeter)
	{
		return fgetcsv($handle, 0, $delimeter);
	}
}