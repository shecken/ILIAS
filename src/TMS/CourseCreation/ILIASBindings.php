<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

use ILIAS\TMS\Wizard;
use ILIAS\TMS\Translations as TranslationDecorator;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * ILIAS Bindings for TMS-Booking process.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIASBindings implements Wizard\ILIASBindings {
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
	 * @var int
	 */
	protected $parent_ref_id;

	/**
	 * @var \ILIAS\TMS\Translations
	 */
	protected $translations;

	final public function __construct(\ilCtrl $ctrl, $gui, array $parent_guis, $parent_cmd, $parent_ref_id, TranslationDecorator $translations) {
		assert('is_object($gui)');
		assert('is_string($parent_cmd)');
		$this->ctrl = $ctrl;
		$this->gui = $gui;
		$this->parent_guis = $parent_guis;
		$this->parent_cmd = $parent_cmd;
		$this->parent_ref_id = $parent_ref_id;
		$this->translations = $translations;
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
		return $this->translations->getTxt($id);
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
		$last_gui = $this->parent_guis[count($this->parent_guis)-1];
		$this->ctrl->setParameterByClass($last_gui, "ref_id", $this->parent_ref_id);
		$this->ctrl->redirectByClass($this->parent_guis, $this->parent_cmd);
	}
}
