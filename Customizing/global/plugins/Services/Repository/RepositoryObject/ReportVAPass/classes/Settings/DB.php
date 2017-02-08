<?php

interface DB
{
	/**
	 * Install everthing needed like tables or something else
	 */
	public function install();

	/**
	 * Read a sinfle settings entry
	 *
	 * @param int 		$obj_id
	 *
	 * @return VAPass
	 */
	public function read($obj_id);

	/**
	 * Insert a ew VA Pass into DB
	 *
	 * @param VAPass 	$va_pass
	 */
	public function insert(VAPass $va_pass);

	/**
	 * Update an existing VAPass
	 *
	 * @param VAPass 	$va_pass
	 */
	public function update(VAPass $va_pass);

	/**
	 * Delete a VAPass
	 *
	 * @param int 		$obj_id
	 */
	public function delete($obj_id);
}
