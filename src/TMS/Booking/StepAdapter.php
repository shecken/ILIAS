<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

use ILIAS\TMS\Wizard as W;

/**
 * This adapts the steps from the bookings to the steps from Wizard.
 */
class StepAdapter implements W\Step {
	/**
	 * @var	Step
	 */
	protected $wrapped;

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	/**
	 * Id of user the booking is made for.
	 *
	 * @var int
	 */
	protected $user_id;

	public function __construct(Step $wrapped, $crs_ref_id, $user_id) {
		assert('is_int($crs_ref_id)');
		assert('is_int($user_id)');
		$this->wrapped = $wrapped;
		$this->crs_ref_id = $crs_ref_id;
		$this->user_id = $user_id;
	}

	/**
	 * @inheritdocs
	 */
	public function getLabel() {
		return $this->wrapped->getLabel();
	}

	/**
	 * @inheritdocs
	 */
	public function getDescription() {
		return $this->wrapped->getDescription();
	}

	/**
	 * @inheritdocs
	 */
	public function appendToStepForm(\ilPropertyFormGUI $form) {
		return $this->wrapped->appendToStepForm($form, $this->user_id);
	}

	/**
	 * @inheritdocs
	 */
	public function getData(\ilPropertyFormGUI $form) {
		return $this->wrapped->getData($form);
	}

	/**
	 * @inheritdocs
	 */
	public function addDataToForm(\ilPropertyFormGUI $form, $data) {
		return $this->wrapped->addDataToForm($form, $data);
	}

	/**
	 * @inheritdocs
	 */
	public function appendToOverviewForm(\ilPropertyFormGUI $form, $data) {
		return $this->wrapped->appendToOverviewForm($data, $form, $this->user_id);
	}

	/**
	 * @inheritdocs
	 */
	public function	processStep($data) {
		return $this->wrapped->processStep($this->crs_ref_id, $this->user_id, $data);
	}
}
