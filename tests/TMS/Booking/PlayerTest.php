<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Booking;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class BookingPlayerForTest extends Booking\Player {
	public function _getSortedSteps() {
		return $this->getSortedSteps();
	}
	public function _getApplicableSteps() {
		return $this->getApplicableSteps();
	}
	public function _getUserId() {
		return $this->getUserId();
	}
}

class TMS_Booking_PlayerTest extends PHPUnit_Framework_TestCase {
	public function test_getUserId() {
		$user_id = 42;
		$player = new BookingPlayerForTest([], 0, $user_id);
		$this->assertEquals($user_id, $player->_getUserId());
	}

	public function test_getSortedSteps() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getApplicableSteps"])
			->disableOriginalConstructor()
			->getMock();

		$component1 = $this->createMock(Booking\Step::class);
		$component2 = $this->createMock(Booking\Step::class);
		$component3 = $this->createMock(Booking\Step::class);

		$component1
			->expects($this->atLeast(1))
			->method("getPriority")
			->willReturn(2);
		$component2
			->expects($this->atLeast(1))
			->method("getPriority")
			->willReturn(3);
		$component3
			->expects($this->atLeast(1))
			->method("getPriority")
			->willReturn(1);

		$player
			->expects($this->once())
			->method("getApplicableSteps")
			->willReturn([$component1, $component2, $component3]);

		$steps = $player->_getSortedSteps();

		$this->assertEquals([$component3, $component1, $component2], $steps);
	}

	public function test_getApplicableSteps() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getComponentsOfType", "getUserId"])
			->disableOriginalConstructor()
			->getMock();

		$user_id = 23;
		$player
			->expects($this->atLeast(1))
			->method("getUserId")
			->willReturn($user_id);

		$component1 = $this->createMock(Booking\Step::class);
		$component2 = $this->createMock(Booking\Step::class);
		$component3 = $this->createMock(Booking\Step::class);

		$component1
			->expects($this->atLeast(1))
			->method("isApplicableFor")
			->with($user_id)
			->willReturn(true);
		$component2
			->expects($this->atLeast(1))
			->method("isApplicableFor")
			->with($user_id)
			->willReturn(false);
		$component3
			->expects($this->atLeast(1))
			->method("isApplicableFor")
			->with($user_id)
			->willReturn(true);

		$player
			->expects($this->once())
			->method("getComponentsOfType")
			->with(Booking\Step::class)
			->willReturn([$component1, $component2, $component3]);

		$steps = $player->_getApplicableSteps();

		$this->assertEquals([$component1, $component3], $steps);
	}
}
