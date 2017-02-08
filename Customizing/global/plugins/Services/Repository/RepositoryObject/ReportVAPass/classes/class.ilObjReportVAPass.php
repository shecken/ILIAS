<?php

require_once("./Services/Repository/classes/class.ilObjectPlugin.php");

use \CaT\Plugins\ReportVAPass;

class ilObjReportVAPass extends ilObjectPlugin
{
	/**
	 * @var CaT\Plugins\ReportVAPass\Settings\VAPass
	 */
	protected $va_pass_settings;

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
		$post = $_POST;
		$this->va_pass_settings = $this->getActions()->create($post);
	}

	/**
	 * Read data from db
	 */
	public function doRead()
	{
		$this->va_pass_settings = $this->getActions()->read();
	}

	/**
	 * Update data
	 */
	public function doUpdate()
	{
		$this->getActions()->update($this->va_pass_settings);
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
		$new_obj->setSettings($this->va_pass_settings->withOnline(false));
		$new_obj->update();
	}

	/**
	 * @return ilActions
	 */
	public function getActions()
	{
		if ($this->actions === null) {
			$this->actions = new ReportVAPass\ilActions($this, $this->getVAPassDB());
		}
		return $this->actions;
	}

	protected function getVAPassDB()
	{
		return $this->plugin->getVAPassDB();
	}

	public function setSettings(ReportVAPass\Settings\VAPass $va_pass)
	{
		$this->va_pass_settings = $va_pass;
	}

	public function getSettings()
	{
		return $this->va_pass_settings;
	}
}
