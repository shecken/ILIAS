<?php

require_once 'Services/Object/classes/class.ilObjectAccess.php';
require_once 'Services/AccessControl/classes/class.ilConditionHandler.php';
class ilObjManualAssessmentAccess extends ilObjectAccess {
	/**
	 * @inheritdoc
	 */
	public function _getCommands() {
		$commands = array(
			array("permission" => "read", "cmd" => "", "lang_var" => "show", "default" => true)
			,array("permission" => "write", "cmd" => "edit", "lang_var" => "edit", "default" => false)
		);
		return $commands;
	}
}