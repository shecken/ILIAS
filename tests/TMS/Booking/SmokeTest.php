<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Booking;

class DummyStep implements Booking\Step {
	public function getLabel() {}
	public function getDescription() {}
	public function getPriority() {}
	public function getForm(array $post = null) {}
	public function getData(\ilPropertyForm $form) {}
	public function appendOverview($data, \ilPropertyForm $form) {}
	public function	processStep($data) {}
}

class TMS_Booking_SmokeTest extends PHPUnit_Framework_TestCase {
	public function test_instantiateStep() {
		$step = new DummyStep();

		$this->assertInstanceOf(Booking\Step::class, $step);
	}
}
