<?php

namespace CaT\IliasUserOrguImport\Data;

interface DataExtractor {
	/**
	 * Get the content of file renaming rows according to field conversions.
	 * We assume row titles are in the first row.
	 *
	 * @param	string	$path_to_file
	 * @param	string[string]	$field_conversions
	 * @return	mixed[][]
	 */
	public function extractContent($path_to_file, array $field_conversion);
}