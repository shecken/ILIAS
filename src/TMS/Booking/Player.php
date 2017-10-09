<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the booking of one spefic course in a row, gathers user
 * input and afterwards completes the booking.
 */
class Player {
	use ilHandlerObjectHelper;

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
	 * @param	\ArrayAccess|array $dic
	 * @param	int	$crs_ref_id 	course that should get booked
	 * @param	int	$usr_id			the usr the booking is made for
	 */
	public function __construct($dic, $crs_ref_id, $usr_id) {
		assert('is_array($dic) || ($dic instanceof \ArrayAccess)');
		assert('is_int($crs_ref_id)');
		assert('is_int($usr_id)');
		$this->dic = $dic;
		$this->crs_ref_id = $crs_ref_id;
		$this->usr_id = $usr_id;
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
