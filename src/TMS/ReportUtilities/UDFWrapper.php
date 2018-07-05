<?php
namespace ILIAS\TMS\ReportUtilities;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

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
	 * Add UDFs to "master"-space and hook to a table/field providing the user's id.
	 *
	 * @param TableRelations\TableFactory 		$tf
	 * @param Filter\PredicateFactory 			$pf
	 * @param TableRelations\Tables\TableSpace 	$space
	 * @param TableRelations\Tables\Table 		$usr_table
	 * @param string 							$usr_id_field_name
	 * @return TableRelations\Tables\TableSpace
	 */
	public function appendUDFs(
		TableRelations\TableFactory $tf,
		Filter\PredicateFactory $pf,
		TableRelations\Tables\TableSpace $space,
		TableRelations\Tables\Table $usr_table,
		$usr_id_field_name
	);

}