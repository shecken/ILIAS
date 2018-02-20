<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

use ILIAS\TMS\Wizard;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * ILIAS Bindings for TMS-Booking process.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIASBindings implements Wizard\ILIASBindings {
	/**
	 * @var	ilLanguage
	 */
	protected $lng;

	/**
	 * @var	ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var object
	 */
	protected $gui;

	/**
	 * @var string[] 
	 */
	protected $parent_guis;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	/**
	 * @var string
	 */
	protected $player_title;

	/**
	 * @var string
	 */
	protected $confirm_button_label;

	/**
	 * @var string
	 */
	protected $overview_description;

	final public function __construct(\ilLanguage $lng, \ilCtrl $ctrl, $gui, array $parent_guis, $parent_cmd) {
		assert('is_object($gui)');
		assert('is_string($parent_cmd)');
		$this->lng = $lng;
		$this->ctrl = $ctrl;
		$this->lng->loadLanguageModule('tms');
		$this->gui = $gui;
		$this->parent_guis = $parent_guis;
		$this->parent_cmd = $parent_cmd;
	}

	/**
	 * @inheritdocs
	 */
	public function getForm() {
		$form = new \ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this->gui));
		$form->setShowTopButtons(true);
		return $form;
	}

	/**
	 * @inheritdocs
	 */
	public function txt($id) {
		if ($id === "abort") {
			$id = "cancel";
		}
		else if ($id === "next") {
			$id = "btn_next";
		}
		else if ($id == "aborted") {
			$id = "process_aborted";
		}
		else if ($id == "previous") {
			$id = "btn_previous";
		}
		else if ($id == "title") {
			$id = "create_course_from_template";	
		}
		else if ($id == "overview_description") {
			$id = $summary;
		}
		return $this->lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	public function redirectToPreviousLocation($messages, $success) {
		if (count($messages)) {
			$message = join("<br/>", $messages);
			if ($success) {
				\ilUtil::sendSuccess($message, true);
			}
			else {
				\ilUtil::sendInfo($message, true);
			}
		}
		$this->ctrl->redirectByClass($this->parent_guis, $this->parent_cmd);
	}
}
