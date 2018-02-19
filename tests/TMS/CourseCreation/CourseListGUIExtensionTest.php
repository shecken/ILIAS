<?php

use ILIAS\TMS\CourseCreation\CourseListGUIExtension;

class  _TMS_CourseCreation_CourseListGUIExtension_Parent {
	public $commands = [];
	public function getCommands() {
		return $this->commands;
	}
}

class _TMS_CourseCreation_CourseListGUIExtension extends _TMS_CourseCreation_CourseListGUIExtension_Parent {
	use CourseListGUIExtension;

	public $create_course_cmd = "CREATE_COURSE";
	protected function getCreateCourseCommand() {
		return $this->create_course_cmd;
	}

	public $create_course_cmd_link = "CREATE_COURSE_LINK";
	protected function getCreateCourseCommandLink() {
		return $this->create_course_cmd_link;
	}

	public $create_course_lng_var = "CREATE_COURSE_LNG_VAR";
	protected function getCreateCourseCommandLngVar() {
		return $this->create_course_lng_var;
	}

	public $create_course_access_granted = true;
	protected function getCreateCourseAccessGranted() {
		return $this->create_course_access_granted;
	}
}

class TMS_CourseCreation_CourseListGUIExtensionTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->gui_fake = new _TMS_CourseCreation_CourseListGUIExtension();
	}

	public function test_enhances_getCommands() {
		$base = [1,2,3,4];
		$this->gui_fake->commands = $base;
		$commands = $this->gui_fake->getCommands();
		$this->assertCount(count($base)+1, $commands);
	}

	public function test_inserts_create_command_in_getCommands() {
		$commands = $this->gui_fake->getCommands();
		$expected = 
			[["cmd" => $this->gui_fake->create_course_cmd
			, "link" => $this->gui_fake->create_course_cmd_link
			, "frame" => ""
			, "lang_var" => $this->gui_fake->create_course_lng_var
			, "txt" => null
			, "granted" => $this->gui_fake->create_course_access_granted
			, "access_info" => null
			, "img" => null
			, "default" => null
			]];
		$this->assertEquals($expected, $commands);
	}
}
