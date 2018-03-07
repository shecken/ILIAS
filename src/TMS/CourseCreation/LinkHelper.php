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
	 * @param	string[]	$parent_guis
	 * @param	string	$parent_cmd
	 * @param	int	$parent_ref_id
	 * @param	int|string $template_ref_id
	 * @return	string
	 */
	protected function getCreateCourseCommandLink($parent_guis, $parent_cmd, $parent_ref_id, $template_ref_id, $async = false) {
		assert('is_string($parent_cmd)');
		assert('is_int($parent_ref_id)');
		assert('is_int($template_ref_id) || is_string($template_ref_id)');
		$ctrl = $this->getCtrl();
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_guis", implode(".", $parent_guis));
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_cmd", $parent_cmd);
		$ctrl->setParameterByClass("ilCourseCreationGUI", "parent_ref_id", $parent_ref_id);
		$ctrl->setParameterByClass("ilCourseCreationGUI", "ref_id", $template_ref_id);
		return $ctrl->getLinkTargetByClass(["ilRepositoryGUI", "ilCourseCreationGUI"], $this->getCreateCourseCommand(), "", $async);
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLngVar() {
		$lng = $this->getLng();
		$lng->loadLanguageModule("tms");
		return "create_course_from_template";
	}

	/**
	 * @return	string
	 */
	protected function getCreateCourseCommandLabel() {
		return $this->getLng()->txt($this->getCreateCourseCommandLngVar());
	}

	/**
	 * @param	array<string,CourseTemplateInfo[]>	$info
	 * @param	string	$select_name
	 * @return	\ilGroupableSelectInputGUI
	 */
	protected function getGroupableSelectInputGUIForCourseTemplates(array $info, $select_name) {
		assert('is_string($select_name)');

		ksort($info, SORT_NATURAL);
		foreach ($info as $k => $is) {
			$group = [];
			foreach($is as $i) {
				$group[$i->getRefId()] = $i->getTitle();
			}
			asort($group, SORT_NATURAL);
			$info[$k] = $group;
		}

		require_once("Services/Form/classes/class.ilGroupableSelectInputGUI.php");
		$select = new \ilGroupableSelectInputGUI("", $select_name);
		$select->setGroups($info);
		return $select;
	}

	/**
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	CourseTemplateInfo[]	$info
	 * @param	string[]	$parent_guis
	 * @param	string		$parent_cmd
	 * @param	int			$parent_ref_id
	 * @return	ILIAS\UI\Component\Modal\Modal
	 */
	protected function getCourseTemplateSelectionModal(\ILIAS\UI\Factory $ui_factory, array $info, array $parent_guis, $parent_cmd, $parent_ref_id) {
		assert('is_string($parent_cmd)');
		assert('is_int($parent_ref_id)');
		$placeholder = "_REF_ID_";
		$link = $this->getCreateCourseCommandLink($parent_guis, $parent_cmd, $parent_ref_id, $placeholder, true);
		$select_name = "course_template_select";

		$next_button = $ui_factory->button()
			->standard(
				ucfirst($this->g_lng->txt("next")),
				""
			)
			->withAdditionalOnLoadCode(function($id) use ($link, $select_name, $placeholder) {
				return "$('#$id').on('click', function(ev) {
					var link = '$link';
					var ref_id = $('select[name^=$select_name]').val();
					link = link.replace('$placeholder', ref_id);
					window.location.href = link;
					ev.preventDefault();
				});";
			});

		$select = $this->getGroupableSelectInputGUIForCourseTemplates($info, $select_name);

		return $ui_factory->modal()
			->roundtrip(
				$this->getLng()->txt("choose_course_template"),
				$ui_factory->legacy($select->render())
			)
			->withActionButtons([$next_button]);
	}

	/**
	 * @param	\ILIAS\UI\Factory $ui_factory
	 * @param	\ILIAS\UI\Factory $ui_renderer
	 * @param	\ilToolbarGUI $toolbar
	 * @param	CourseTemplateInfo[]	$info
	 * @param	string[]	$parent_guis
	 * @param	string		$parent_cmd
	 * @param	int			$parent_ref_id
	 * @return void
	 */
	protected function addCourseTemplateSelectionModalToToolbar(\ILIAS\UI\Factory $ui_factory, \ILIAS\UI\Renderer $ui_renderer, \ilToolbarGUI $toolbar, array $info, array $parent_guis, $parent_cmd, $parent_ref_id) {
		assert('is_int($parent_ref_id)');
		assert('is_string($parent_cmd)');
		$modal = $this->getCourseTemplateSelectionModal($ui_factory, $info, $parent_guis, $parent_cmd, $parent_ref_id);
		$button = $ui_factory->button()
			->primary(
				$this->getCreateCourseCommandLabel(),
				""
			)->withOnClick($modal->getShowSignal());

		$toolbar->addText($ui_renderer->render([$button, $modal]));
	}
}
