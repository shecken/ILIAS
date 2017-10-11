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
	public function _getProcessState() {
		return $this->getProcessState();
	}
}

class TMS_Booking_PlayerTest extends PHPUnit_Framework_TestCase {
	public function test_getUserId() {
		$user_id = 42;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new BookingPlayerForTest([], 0, $user_id, $db);
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

	public function test_getProcessState_existing() {
		$course_id = 42;
		$user_id = 23;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$state = $this->getMockBuilder(Booking\ProcessState::class)
			->disableOriginalConstructor()
			->getMock();
		$player = new BookingPlayerForTest([], $course_id, $user_id, $db);

		$db
			->expects($this->once())
			->method("load")
			->with($course_id, $user_id)
			->willReturn($state);

		$state2 = $player->_getProcessState();
		$this->assertEquals($state, $state2);
	}

	public function test_getProcessState_new() {
		$course_id = 42;
		$user_id = 23;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new BookingPlayerForTest([], $course_id, $user_id, $db);

		$db
			->expects($this->once())
			->method("load")
			->with($course_id, $user_id)
			->willReturn(null);

		$state = $player->_getProcessState();
		$expected = new Booking\ProcessState($course_id, $user_id, 0);
		$this->assertEquals($expected, $state);
	}

	public function test_buildView_data_not_ok() {
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

		$post = ["foo" => "bar"];
		$step2
			->expects($this->once())
			->method("getForm")
			->with($post)
			->willReturn($form);

		$step2
			->expects($this->once())
			->method("getData")
			->with($form)
			->willReturn(null);

		$html = "HTML OUTPUT STEP 2";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->buildView($post);

		$this->assertEquals($html, $view);
	}

	public function test_buildView_data_ok() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState"])
			->disableOriginalConstructor()
			->getMock();

		$form_step2 = $this->createMock(\ilPropertyFormGUI::class);
		$form_step3 = $this->createMock(\ilPropertyFormGUI::class);

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

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$post = ["foo" => "bar"];
		$step2
			->expects($this->once())
			->method("getForm")
			->with($post)
			->willReturn($form_step2);

		$data = ["bar" => "baz"];
		$step2
			->expects($this->once())
			->method("getData")
			->with($form_step2)
			->willReturn($data);

		$step3
			->expects($this->once())
			->method("getForm")
			->with(null)
			->willReturn($form_step3);

		$html = "HTML OUTPUT STEP 3";
		$form_step3
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->buildView($post);

		$this->assertEquals($html, $view);
	}

	public function test_buildView_expects_post_on_further_steps() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState"])
			->disableOriginalConstructor()
			->getMock();

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$catched = false;
		try {
			$view = $player->buildView();
			$this->assertFalse("This should not happen.");
		}
		catch (\LogicException $e) {
			$catched = true;		
		}
		$this->assertTrue($catched);
	}

	public function test_buildView_first() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 0;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step2
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

		$step1
			->expects($this->once())
			->method("getForm")
			->with(null)
			->willReturn($form);

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->buildView();

		$this->assertEquals($html, $view);
	}
}
