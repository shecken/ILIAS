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

	public function test_getProcessState() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->disableOriginalConstructor()
			->getMock();

		$db = $this->createMock(Booking\ProcessStateDB);
	}

	public function test_buildView() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$step2
			->expects($this->once())
			->method("getForm")
			->with(null)
			->willReturn($form);

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);
	}
}
