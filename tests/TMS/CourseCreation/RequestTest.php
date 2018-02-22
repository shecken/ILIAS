<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\CourseCreation;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class TMS_CourseCreation_RequestTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->id = 23;
		$this->user_id = 43;
		$this->session_id = "SESSION_ID";
		$this->crs_obj_id = 1337;
		$this->request_ts = new \DateTime("1985-04-05 13:37");
		$this->finished_ts = new \DateTime("now");
		$this->request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_obj_id, $this->request_ts, $this->finished_ts);
	}

	public function test_getId() {
		$this->assertEquals($this->id, $this->request->getId());
	}

	public function test_getUserId() {
		$this->assertEquals($this->user_id, $this->request->getUserId());
	}

	public function test_getSessionId() {
		$this->assertEquals($this->session_id, $this->request->getSessionId());
	}

	public function test_getCourseObjId() {
		$this->assertEquals($this->crs_obj_id, $this->request->getCourseObjId());
	}

	public function test_getRequestedTS() {
		$this->assertEquals($this->request_ts, $this->request->getRequestedTS());
	}

	public function test_getFinishedTS() {
		$this->assertEquals($this->finished_ts, $this->request->getFinishedTS());
	}

	public function test_withDFinishedTS() {
		$new_ts = new \DateTime("2000-12-31 23:59");
		$clone = $this->request->withFinishedTS($new_ts);

		$this->assertEquals($this->finished_ts, $this->request->getFinishedTS());
		$this->assertEquals($new_ts, $clone->getFinishedTS());
	}

	public function test_finishedTS_id_nullable() {
		$request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_obj_id, $this->request_ts, null);
		$this->assertEquals(null, $request->getFinishedTS());
	}
}
