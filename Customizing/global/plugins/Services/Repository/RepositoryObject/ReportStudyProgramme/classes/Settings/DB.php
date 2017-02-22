<?php

namespace CaT\Plugins\ReportStudyProgramme\Settings;

/**
 * Inteface for db abstraction
 */
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
	 * @return ReportStudyProgramme
	 */
	public function read($obj_id);

	/**
	 * Insert a ew VA Pass into DB
	 *
	 * @param ReportStudyProgramme 	$xsp_pass
	 */
	public function insert(ReportStudyProgramme $xsp_pass);

	/**
	 * Update an existing ReportStudyProgramme
	 *
	 * @param ReportStudyProgramme 	$xsp_pass
	 */
	public function update(ReportStudyProgramme $xsp_pass);

	/**
	 * Delete a ReportStudyProgramme
	 *
	 * @param int 		$obj_id
	 */
	public function delete($obj_id);
}
