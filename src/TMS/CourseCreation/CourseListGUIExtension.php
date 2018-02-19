<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Enhances a course list gui with methods required for display of the action to
 * create the course.
 */
trait CourseListGUIExtension {
	/**
	 * Overwritten from ilObjectListGUI. Enhances the supplied commands by
	 * a custom command for the course creation.
	 *
	 * @inheritdocs
	 */
	public function getCommands() {
		$commands = parent::getCommands();
		$commands[] = 
			[ "permission" => "copy"
			, "cmd" => $this->getCreateCourseCommand()
			, "link" => $this->getCreateCourseCommandLink()
			, "frame" => ""
			, "lang_var" => $this->getCreateCourseCommandLngVar()
			, "txt" => $this->getCreateCourseCommandTxt()
			, "granted" => $this->getCreateCourseAccessGranted()
			, "access_info" => null
			, "img" => null
			, "default" => null
			];
		return $commands;
	}

	protected function getCreateCourseCommand() {
	}

	protected function getCreateCourseCommandLink() {
	}

	protected function getCreateCourseCommandLngVar() {
	}

	protected function getCreateCourseCommandTxt() {
	}

	protected function getCreateCourseAccessGranted() {
	}
}
