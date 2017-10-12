<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the booking of one spefic course in a row, gathers user
 * input and afterwards completes the booking.
 */
abstract class Player {
	use ilHandlerObjectHelper;

	const START_WITH_STEP = 0;

	/**
	 * @var	\ArrayAccess
	 */
	protected $dic;

	/**
	 * @var	int
	 */
	protected $crs_ref_id;

	/**
	 * @var	int
	 */
	protected $usr_id;

	/**
	 * @var	ProcessStateDB
	 */
	protected $process_db;

	/**
	 * @param	\ArrayAccess|array $dic
	 * @param	int	$crs_ref_id 	course that should get booked
	 * @param	int	$usr_id			the usr the booking is made for
	 */
	public function __construct($dic, $crs_ref_id, $usr_id, ProcessStateDB $process_db) {
		assert('is_array($dic) || ($dic instanceof \ArrayAccess)');
		assert('is_int($crs_ref_id)');
		assert('is_int($usr_id)');
		$this->dic = $dic;
		$this->crs_ref_id = $crs_ref_id;
		$this->usr_id = $usr_id;
		$this->process_db = $process_db;
	}

	/**
	 * @inheritdoc
	 */
	protected function getDIC() {
		return $this->dic;
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntityRefId() {
		return $this->crs_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	protected function getUserId() {
		return $this->usr_id;
	}

	/**
	 * Build the view for the current step in the booking process.
	 *
	 * @param	array|null	$post
	 * @throws	\LogicException		in case post was required but not supplied
	 * @return	string
	 */
	public function buildView(array $post = null) {
		$state = $this->getProcessState();
		$steps = $this->getSortedSteps();
		$step_number = $state->getStepNumber();
		$current_step = $steps[$step_number];
		if ($step_number > 0) {
			if ($post === null) {
				throw new \LogicException("Missing POST.");
			}
			$form = $current_step->getForm($post);
			$data = $current_step->getData($form);
			if ($data === null) {
				return $form->getHtml();
			}
			$state = $state
				->withNextStep()
				->withStepData($step_number, $data);
			$this->saveProcessState($state);
		}
		if ($step_number+1 == count($steps)) {
			$form = $this->buildOverviewForm($state);
		}
		else {
			if ($step_number > 0) {
				$next_step = $steps[$step_number+1];
			}
			else {
				$next_step = $steps[0];
			}
			$form = $next_step->getForm();
		}
		return $form->getHtml();
	}

	/**
 	 * Build the final overview form.
	 *
	 * @param	ProcessState $state
	 * @return	\ilPropertyFormGUI
	 */
	protected function buildOverviewForm(ProcessState $state) {
	}

	/**
	 * Get a form for the overview.
	 *
	 * @return \ilPropertyFormGUI
	 */
	abstract protected function getOverviewForm();

	/**
	 * Get the state information about the booking process.
	 *
	 * @return	ProcessState
	 */
	protected function getProcessState() {
		$state = $this->process_db->load($this->crs_ref_id, $this->usr_id);
		if ($state !== null) {
			return $state;
		}
		return new ProcessState($this->crs_ref_id, $this->usr_id, self::START_WITH_STEP);
	}

	/**
	 * Save the state information about the booking process.
	 *
	 * @param	ProcessState
	 * @return	void
	 */
	protected function saveProcessState(ProcessState $state) {
		$this->process_db->save($state);
	}

	/**
	 * Get the steps that are applicable for a given user.
	 *
	 * @return	Step[]
	 */
	protected function getApplicableSteps() {
		$steps = $this->getComponentsOfType(Step::class);
		return array_values(array_filter($steps, function($step) {
			return $step->isApplicableFor($this->getUserId());
		}));
	}

	/**
	 * Get the steps for the booking of the couse sorted by period.
	 *
	 * @return 	Step[]
	 */
	protected function getSortedSteps() {
		$steps = $this->getApplicableSteps();
		usort($steps, function (Step $a, Step $b) {
			if ($a->getPriority() < $b->getPriority()) {
				return -1;
			}
			if ($a->getPriority() > $b->getPriority()) {
				return 1;
			}
			return 0;
		});
		return $steps;
	}
} 
