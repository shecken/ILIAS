<?php
include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

class ilObjReportStudyProgrammeListGUI extends ilObjectPluginListGUI
{
	public function initType()
	{
		$this->setType("xsp");
	}

	/**
	 * Get name of gui class handling the commands
	 */
	public function getGuiClass()
	{
		return "ilObjReportStudyProgrammeGUI";
	}

	/**
	 * Get commands
	 */
	public function initCommands()
	{
		return array(
				array("permission" => "read",
					  "cmd" => "showContent",
					  "default" => true)
			  , array("permission" => "write",
					  "cmd" => "editProperties",
					  "txt" => $this->txt("edit"),
					  "default" => false)
				);
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

		$this->plugin->includeClass("class.ilObjReportStudyProgrammeAccess.php");
		if (!\ilObjReportStudyProgrammeAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->txt("status"),
			"value" => $this->txt("offline"));
		}

		return $props;
	}
}
