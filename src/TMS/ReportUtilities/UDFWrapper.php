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
	 * @return array<int, string> 	field_id=>field_name
	 */
	public function getLUAVisibleFields();


	/**
	 * add to "master"-space and hook to $usr_data->field('usr_id')
	 *
	 * @return space
	 */
	public function appendUDFs($space, $usr_table, $usr_id_field);

}