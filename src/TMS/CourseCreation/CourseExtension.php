<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Enhances a course list gui with methods required for display of the action to
 * create the course.
 */
trait CourseListGUIExtension {
	/**
	 * Overwritten from ilObjectListGUI.
	 *
	 * @inheritdocs
	 */
	public function getCommands() {
		return parent::getCommands();
	}
}
