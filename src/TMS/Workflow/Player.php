<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Workflow;

require_once(__DIR__."/../../../Services/Form/classes/class.ilFormSectionHeaderGUI.php");

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the workflow a row, gathers user input and afterwards
 * completes the workflow by processing the steps.
 *
 * TODO: This rather should take the abstract methods via an interface and be final
 * instead of forcing to derive from this class. This will make the ugly init go away.
 */
abstract class Player {
	const START_WITH_STEP = 0;
	const COMMAND_START = "start";
	const COMMAND_ABORT = "abort";
	const COMMAND_NEXT	= "next";
	const COMMAND_CONFIRM = "confirm";
	const COMMAND_PREVIOUS = "previous";

	/**
	 * @var Workflow	
	 */
	protected $workflow;

	/**
	 * @var GUIBindings
	 */
	protected $gui_bindings;

	/**
	 * @var	StateDB
	 */
	protected $state_db;

	public function __construct(GUIBindings $gui_bindings, Workflow $workflow, StateDB $state_db) {
		$this->workflow = $workflow;
		$this->gui_bindings = $gui_bindings;
		$this->state_db = $state_db;
	}

	/**
	 * Process the user input and build the appropriate view.
	 *
	 * The workflow ended when null is returned.
	 *
	 * @param	string|null	$cmd
	 * @param	array|null	$post
	 * @return	string|null
	 */
	public function run($cmd = null, array $post = null) {
		assert('is_null($cmd) || is_string($cmd)');

		if ($cmd === null) {
			$cmd = self::COMMAND_NEXT;
		}

		$state = $this->getState();
		switch ($cmd) {
			case self::COMMAND_START:
				$this->state_db->delete($state);
				return $this->run(self::COMMAND_NEXT, $post);
			case self::COMMAND_ABORT:
				$this->state_db->delete($state);
				$aborted = $this->gui_bindings->txt("aborted");
				return $this->gui_bindings->redirectToPreviousLocation([$aborted], false);
			case self::COMMAND_NEXT:
				return $this->runStep($state, $post);
			case self::COMMAND_PREVIOUS:
				return $this->runPreviousStep($state);
			case self::COMMAND_CONFIRM:
				return $this->finish($state);
		}
		throw new \LogicException("Unknown command: '$cmd'");
	}

	/**
	 * Build the view for the current step in the workflow.
	 *
	 * @param	State	$state
	 * @param	array|null	$post
	 * @return	string
	 */
	protected function runStep(State $state, array $post = null) {
		$steps = $this->workflow->getSteps();
		$step_number = $state->getStepNumber();

		if($step_number < 0) {
			throw new \LogicException("It is impossible that the number of step is smaller than 0.");
		}

		if ($step_number == count($steps)) {
			assert('is_null($post)');
			return $this
				->buildOverviewForm($state)
				->getHtml();
		}

		$current_step = $steps[$step_number];

		$form = $this->gui_bindings->getForm();
		if($step_number > 0) {
			$form->addCommandButton(self::COMMAND_PREVIOUS, $this->gui_bindings->txt("previous"));
		}
		$form->addCommandButton(self::COMMAND_NEXT, $this->gui_bindings->txt("next"));
		$form->addCommandButton(self::COMMAND_ABORT, $this->gui_bindings->txt("abort"));

		$form->setTitle($this->gui_bindings->txt("title"));
		$current_step->appendToStepForm($form);

		// TODO: factor me out
		// This attempts to process the input of the user.
		if ($post) {
			$form->setValuesByArray($post);
			if ($form->checkInput()) {
				$data = $current_step->getData($form);
				if ($data !== null) {
					$state = $state
						->withStepData($step_number, $data)
						->withNextStep();
					$this->state_db->save($state);
					return $this->runStep($state);
				}
			}
		}

		// TODO: factor me out
		// This shows previously inputed data.
		if($state->hasStepData($step_number)) {
			$step_data = $state->getStepData($step_number);
			$current_step->addDataToForm($form, $step_data);
		}

		return $form->getHtml();
	}

	/**
	 * Build the view for the previous step in the workflow.
	 *
	 * @param	State	$state
	 * @return	string
	 */
	protected function runPreviousStep(State $state) {
		$state = $state->withPreviousStep();
		$this->state_db->save($state);
		$step_number = $state->getStepNumber();

		return $this->runStep($state);
	}

	/**
 	 * Build the final overview form.
	 *
	 * @param	State $state
	 * @return	\ilPropertyFormGUI
	 */
	protected function buildOverviewForm(State $state) {
		$steps = $this->workflow->getSteps();
		$form = $this->gui_bindings->getForm();

		$form->addCommandButton(self::COMMAND_PREVIOUS, $this->gui_bindings->txt("previous"));
		$form->addCommandButton(self::COMMAND_CONFIRM, $this->gui_bindings->txt("confirm"));
		$form->addCommandButton(self::COMMAND_ABORT, $this->gui_bindings->txt("abort"));

		$form->setTitle($this->gui_bindings->txt("title"));
		$form->setDescription($this->gui_bindings->txt("overview_description"));

		for($i = 0; $i < count($steps); $i++) {
			$step = $steps[$i];
			$header = new \ilFormSectionHeaderGUI();
			$header->setTitle($step->getLabel());
			$form->addItem($header);
			$data = $state->getStepData($i);
			$step->appendToOverviewForm($data, $form);
		}

		return $form;
	}

	/**
	 * Finish the workflow by actually processing the steps.
	 *
	 * @param	State	$state
	 * @return	void
	 */
	protected function finish(State $state) {
		$steps = $this->workflow->getSteps();
		assert('$state->getStepNumber() == count($steps)');

		if ($state->getStepNumber() !== count($steps)) {
			throw new \LogicException("User did not work through the workflow.");
		}

		$messages = [];
		for ($i = 0; $i < count($steps); $i++) {
			$step = $steps[$i];
			$data = $state->getStepData($i);
			$message = $step->processStep($data);
			if ($message) {
				$messages[] = $message;
			}
		}
		$this->state_db->delete($state);
		$this->gui_bindings->redirectToPreviousLocation($messages, true);
	}

	/**
	 * Get the state information about the booking process.
	 *
	 * @return	State
	 */
	protected function getState() {
		$state = $this->state_db->load($this->workflow->getId());
		if ($state !== null) {
			return $state;
		}
		return new State($this->workflow->getId(), self::START_WITH_STEP);
	}
} 
