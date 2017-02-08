<?php

require_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once("./vendor/autoload.php");

use \CaT\Plugins\ReportVAPass;

class ilObjReportVAPass extends ilObjectPlugin
{
	public function __construct($a_ref_id = 0)
	{
		$this->settings = null;
		$this->actions = null;

		parent::__construct($a_ref_id);

		// read settings again from the database if we already
		// have a ref id, as 0 for ref_id means, we are just
		// creating the object.
		if ($a_ref_id !== 0) {
			$this->doRead();
		}
	}

	public function initType()
	{
		$this->setType("xvap");
	}

	/**
	 * Create object
	 */
	public function doCreate()
	{
	}

	/**
	 * Read data from db
	 */
	public function doRead()
	{
	}

	/**
	 * Update data
	 */
	public function doUpdate()
	{
	}

	/**
	 * Delete data from db
	 */
	public function doDelete()
	{
	}

	/**
	 * Do Cloning
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id)
	{
	}
}
