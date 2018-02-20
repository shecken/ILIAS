<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Enhances a course access handler with methods required for the course creation.
 */
trait CourseAccessExtension {
	function _checkAccessExtension($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id) {
		// TODO: implement me properly
		// Check if CourseCreation-plugin exists
		// Check if course is a template
		// Check if user may copy course
		// Check if user can see course creation plugin
		if ($a_cmd === "create_course_from_template") {
			return true;
		}
	}
}
