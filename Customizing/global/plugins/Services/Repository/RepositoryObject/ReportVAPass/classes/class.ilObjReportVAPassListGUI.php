<?php
include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

class ilObjReportVAPassListGUI extends ilObjectPluginListGUI
{
	public function initType()
	{
		$this->setType("xvap");
	}

	/**
	 * Get name of gui class handling the commands
	 */
	public function getGuiClass()
	{
		return "ilObjReportVAPassGUI";
	}

	/**
	 * Get item properties
	 *
	 * @return 	array 	array of property arrays:
	 *					"alert" (boolean) => display as an alert property (usually in red)
	 *					"property" (string) => property name
	 *					"value" (string) => property value
	 */
	public function getProperties()
	{
		global $lng, $ilUser;

		$props = array();

		$this->plugin->includeClass("class.ilObjReportVAPassAccess.php");
		if (!\ilObjReportVAPassAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->txt("status"),
			"value" => $this->txt("offline"));
		}

		return $props;
	}
}
