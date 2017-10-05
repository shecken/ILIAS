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
	 * @param	\ArrayAccess|array $dic
	 * @param	int	$crs_ref_id
	 */
	public function __construct($dic, $crs_ref_id) {
		assert('is_int($crs_ref_id)');
		assert('is_array($dic) || ($dic instanceof \ArrayAccess)');
		$this->dic = $dic;
		$this->crs_ref_id = $crs_ref_id;
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
	 * Get the steps for the booking of the couse sorted by period.
	 *
	 * @return 	Step[]
	 */
	protected function getSortedSteps() {
		$steps = $this->getComponentsOfType(Step::class);
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
