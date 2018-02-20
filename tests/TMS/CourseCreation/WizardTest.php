<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\CourseCreation;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class _CourseCreationWizard extends CourseCreation\Wizard{
	public function _getSortedSteps() {
		return $this->getSortedSteps();
	}
	public function _getApplicableSteps() {
		return $this->getApplicableSteps();
	}
	public function _getUserId() {
		return $this->getUserId();
	}
	public function _getTimestamp() {
		return $this->getTimestamp();
	}
	public function _getEntityRefId() {
		return $this->getEntityRefId();
	}
	public function _getDIC() {
		return $this->getDIC();
	}
}

class TMS_CourseCreation_WizardTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->step_count = 0;
	}

	public function createStepMock() {
		$this->step_count++;
		return $this->getMockBuilder(CourseCreation\Step::class)
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMockClassName("CourseCreationStep".$this->step_count)
			->getMock();		
	}

	public function test_getId() {
		$ts = 9087;
		$wizard_id = "CourseCreation_1_2_$ts";
		$wizard = new _CourseCreationWizard([], 1, 2, $ts);
		$this->assertSame($wizard_id, $wizard->getId());
	}

	public function test_getDIC() {
		$dic = ["my" => "container"];
		$wizard = new _CourseCreationWizard($dic, 0, 0, 0);
		$this->assertSame($dic, $wizard->_getDIC());
	}

	public function test_getUserId() {
		$user_id = 42;
		$wizard = new _CourseCreationWizard([], $user_id, 0, 0);
		$this->assertEquals($user_id, $wizard->_getUserId());
	}

	public function test_getEntityRefId() {
		$crs_id = 23;
		$wizard = new _CourseCreationWizard([], 0, $crs_id, 0);
		$this->assertEquals($crs_id, $wizard->_getEntityRefId());
	}

	public function test_getTimestamp() {
		$timestamp = 1337;
		$wizard = new _CourseCreationWizard([], 0, 0, $timestamp);
		$this->assertEquals($timestamp, $wizard->_getTimestamp());
	}

	public function test_getSortedSteps() {
		$wizard = $this->getMockBuilder(_CourseCreationWizard::class)
			->setMethods(["getApplicableSteps"])
			->disableOriginalConstructor()
			->getMock();

		$component1 = $this->createStepMock();
		$component2 = $this->createStepMock();
		$component3 = $this->createStepMock();

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

		$wizard
			->expects($this->once())
			->method("getApplicableSteps")
			->willReturn([$component1, $component2, $component3]);

		$steps = $wizard->_getSortedSteps();

		$this->assertEquals([$component3, $component1, $component2], $steps);
	}

	public function test_getApplicableSteps() {
		$wizard = $this->getMockBuilder(_CourseCreationWizard::class)
			->setMethods(["getComponentsOfType", "getUserId", "getComponentClass"])
			->disableOriginalConstructor()
			->getMock();

		$user_id = 23;
		$wizard
			->expects($this->atLeast(1))
			->method("getUserId")
			->willReturn($user_id);

		$component1 = $this->createStepMock();
		$component2 = $this->createStepMock();
		$component3 = $this->createStepMock();

		$component1
			->expects($this->once())
			->method("setUserId")
			->with($user_id);
		$component1
			->expects($this->atLeast(1))
			->method("isApplicable")
			->willReturn(true);
		$component2
			->expects($this->once())
			->method("setUserId")
			->with($user_id);
		$component2
			->expects($this->atLeast(1))
			->method("isApplicable")
			->willReturn(false);
		$component3
			->expects($this->once())
			->method("setUserId")
			->with($user_id);
		$component3
			->expects($this->atLeast(1))
			->method("isApplicable")
			->willReturn(true);

		$wizard
			->expects($this->once())
			->method("getComponentsOfType")
			->with($this->equalTo(CourseCreation\Step::class))
			->willReturn([$component1, $component2, $component3]);

		$steps = $wizard->_getApplicableSteps();

		$this->assertEquals([$component1, $component3], $steps);
	}


	public function test_getSteps() {
		$user_id = 1;
		$crs_ref_id = 2;

		$wizard = $this->getMockBuilder(_CourseCreationWizard::class)
			->setMethods(["getSortedSteps"])
			->setConstructorArgs([[], $user_id, $crs_ref_id, 0])
			->getMock();

		$component1 = $this->createStepMock();
		$component2 = $this->createStepMock();
		$component3 = $this->createStepMock();

		$wizard
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$component1, $component2, $component3]);

		$steps = $wizard->getSteps();

		$this->assertCount(3, $steps);
		$this->assertEquals([$component1, $component2, $component3], $steps);
	}
}
