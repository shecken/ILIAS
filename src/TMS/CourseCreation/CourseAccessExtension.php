<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

require_once("Services/Component/classes/class.ilPluginAdmin.php");

/**
 * Enhances a course access handler with methods required for the course creation.
 */
trait CourseAccessExtension {
	static function _checkAccessExtension($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id) {
		if ($a_cmd === "create_course_from_template") {
			// TODO: implement me properly
			if (!\ilPluginAdmin::isPluginActive("xccr") || !\ilPluginAdmin::isPluginActive("xcps")) {
				return false;
			}
			if (!self::_isTemplateCourse($a_ref_id)) {
				return false;
			}
			global $DIC;
			$access = $DIC->access();
			if (!$access->checkAccessOfUser($a_user_id, "copy", "initTargetSelection", $a_ref_id, "crs")) {
				return false;
			}
			if (!self::_userCanSeeCopySettingsObject($a_user_id, $a_ref_id)) {
				return false;
			}
			return true;
		}
	}

	static function _isTemplateCourse($ref_id) {
		global $DIC;
		$tree = $DIC->repositoryTree();
		$node = $tree->getNodeData($ref_id);
		return count($tree->getSubTree($node, false, ["xcps"])) > 0;
	}

	static function _userCanSeeCopySettingsObject($user_id, $ref_id) {
		global $DIC;
		$tree = $DIC->repositoryTree();
		$access = $DIC->access();

		$node = $tree->getNodeData($ref_id);
		foreach($tree->getSubTree($node, false, ["xcps"]) as $cp_ref_id) {
			if ($access->checkAccessOfUser($user_id, "visible", "info", $ref_id, "xcps")) {
				return true;
			}
		}
		return false;
	}
}
