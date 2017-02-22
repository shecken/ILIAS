<?php

require_once("./Services/Repository/classes/class.ilObjectPlugin.php");

use \CaT\Plugins\ReportStudyProgramme;

class ilObjReportStudyProgramme extends ilObjectPlugin
{
	/**
	 * @var CaT\Plugins\ReportStudyProgramme\Settings\ReportStudyProgramme
	 */
	protected $xsp_pass_settings;

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
		$this->setType("xsp");
	}

	/**
	 * Create object
	 */
	public function doCreate()
	{
		$post = $_POST;
		$this->xsp_pass_settings = $this->getActions()->create($post);
	}

	/**
	 * Read data from db
	 */
	public function doRead()
	{
		$this->xsp_pass_settings = $this->getActions()->read();
	}

	/**
	 * Update data
	 */
	public function doUpdate()
	{
		$this->getActions()->update($this->xsp_pass_settings);
	}

	/**
	 * Delete data from db
	 */
	public function doDelete()
	{
		$this->getActions()->delete();
	}

	/**
	 * Do Cloning
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id)
	{
		$new_obj->setSettings($this->xsp_pass_settings->withOnline(false));
		$new_obj->update();
	}

	/**
	 * @return ilActions
	 */
	public function getActions()
	{
		if ($this->actions === null) {
			$this->actions = new ReportStudyProgramme\ilActions($this, $this->getReportStudyProgrammeDB());
		}
		return $this->actions;
	}

	protected function getReportStudyProgrammeDB()
	{
		return $this->plugin->getReportStudyProgrammeDB();
	}

	public function setSettings(ReportStudyProgramme\Settings\ReportStudyProgramme $xsp_pass)
	{
		$this->xsp_pass_settings = $xsp_pass;
	}

	public function getSettings()
	{
		return $this->xsp_pass_settings;
	}
}
