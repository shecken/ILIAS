<?php
namespace ILIAS\TMS\ReportUtilities;

/**
 * Provide centralized access to ILIAS' UDFs.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-traning.de>
 */
interface UDFWrapper {

	/**
	 * Get the fields visible in local user administration.
	 *
	 * @return array<int, array>
	 */
	public function getLUAVisibleFields();


	/**
	 * Get the field's value for the given user.
	 *
	 * @param string 	$name
	 * @param int 		$usr_id
	 * @return mixed
	 */
	public function getFieldValue($field_name, $usr_id);

}