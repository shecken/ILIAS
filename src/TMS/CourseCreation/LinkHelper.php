<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Some common methods to help with the creation of links to the course creation.
 */
trait LinkHelper {
	/**
	 * @return	\ilCtrl
	 */
	abstract protected function getCtrl();

	/**
	 * @return \ilLanguage
	 */
	abstract protected function getLng();

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommand() {
		return "create_course_from_template";
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLink() {
		$ctrl = $this->getCtrl();
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_ref_id", $this->parent_ref_id);
		$ctrl->setParameterByClass("ilCourseCreationGUI", "ref_id", $this->ref_id);
		return $ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilCourseCreationGUI"], $this->getCreateCourseCommand());
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLngVar() {
		$lng = $this->getLng();
		$this->lng->loadLanguageModule("tms");
		return "create_course_from_template";
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLabel() {
		return $this->getLng()->txt($this->getCreateCourseCommandLngVar());
	}
}
