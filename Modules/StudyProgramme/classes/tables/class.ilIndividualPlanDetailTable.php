<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");

/**
 * Shows study programme and course of members VA Pass as a table
 *
 * @author Daniel Weise <daniel.weise@conceptsandtraining.de>
 */

class ilIndividualPlanDetailTable {

	public function __construct()
	{
		global $ilDB;

		$this->g_db = $ilDB;
	}

	public function getGevSettings()
	{
		return gevSettings::getInstance();
	}

	public function getVAPassAccountableFieldId()
	{
		return $this->getGevSettings()->getVAPassAccountableFieldId();
	}

	public function getVAPassPassingTypeFieldId()
	{
		return $this->getGevSettings()->getVAPassPassingTypeFieldId();
	}

	public function getVAPassOptionalTypeId() {
		return $this->getGevSettings()->getVAPassOptionalTypeId();
	}


	public function getAccountable($obj_id, $field_id) {
		$query = "SELECT value \n"
				."FROM adv_md_values_text\n"
				."WHERE obj_id = " . $this->g_db->quote($obj_id, "integer") ."\n"
				."   AND field_id = " . $this->g_db->quote($field_id, "integer");
		$res = $this->g_db->query($query);
		return $this->g_db->fetchAssoc($res)['value'];
	}

	public function getLPStatus($obj_id, $usr_id)
	{
		$query = "SELECT status, last_change\n"
				."FROM prg_usr_progress\n"
				."WHERE usr_id = " . $this->g_db->quote($usr_id, "integer") . "\n"
				."   AND prg_id = " . $this->g_db->quote($obj_id, "integer");
		$res = $this->g_db->query($query);
		return  $this->g_db->fetchAssoc($res);
	}

	public function isOptional($obj_id, $field_id)
	{
		$query = "SELECT value\n"
				."FROM adv_md_values_text\n"
				."WHERE obj_id = " . $this->g_db->quote($obj_id, "integer") . "\n"
				."   AND field_id = " . $this->g_db->quote($field_id, "integer");
		$result = $this->g_db->query($query);
		if($this->g_db->numRows($result) == 0) {
			return false;
		}
		$row = $this->g_db->fetchAssoc($result);
		return $row['value'] === "Ja";
	}
}