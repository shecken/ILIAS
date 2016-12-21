<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterGUI.php';
class catFilterOptionGUI extends catFilterGUI
{
	protected $filter;
	protected $path;
	protected $val;
	public function __construct($filter, $path)
	{
		$this->filter = $filter;
		$this->path = $path;
	}
	/**
	 * @inheritdoc
	 */
	public function formElement()
	{
		$checkbox = new ilCheckboxInputGUI($this->filter->label(), "filter[$this->path]");
		$checkbox->setInfo($this->filter->description());
		if ($this->val !== null) {
			$checkbox->setChecked($this->val);
		} else {
			$checkbox->setChecked($this->filter->getChecked());
		}
		return $checkbox;
	}
	public function setValue($val)
	{
		$this->val = $val;
	}
}
