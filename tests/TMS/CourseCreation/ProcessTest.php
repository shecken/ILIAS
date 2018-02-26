<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\CourseCreation;

if (!class_exists(\ilTree::class)) {
	require_once("Services/Tree/classes/class.ilTree.php");
}

class _CourseCreationProcess extends CourseCreation\Process {
	public function _getCopyWizardOptions($request) {
		return $this->getCopyWizardOptions($request);
	}
}

class TMS_CourseCreation_ProcessTest extends PHPUnit_Framework_TestCase {
	public function test_getCopyWizardOptions() {
		$tree = $this->createMock(\ilTree::class);
		$request = $this->createMock(CourseCreation\Request::class);

		$crs_id = 42;

		$request
			->expects($this->once())
			->method("getCourseRefId")
			->willReturn($crs_id);

		$tree
			->expects($this->once())
			->method("getSubTreeIds")
			->with($crs_id)
			->willReturn([1,2,3]);

		$request
			->expects($this->exactly(3))
			->method("getCopyOptionFor")
			->withConsecutive([1], [2], [3])
			->will($this->onConsecutiveCalls(10,20,30));

		$process = new _CourseCreationProcess($tree);

		$expected = [1 => [ "type" => 10], 2 => ["type" => 20], 3 => ["type" => 30]];
		$options = $process->_getCopyWizardOptions($request);

		$this->assertEquals($expected, $options);
	}
}
