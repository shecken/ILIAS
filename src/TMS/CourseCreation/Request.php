<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Encapsulates information about one request for a course creation.
 */
class Request {
	/**
	 * @var	int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var string
	 */
	public $session_id;

	/**
	 * @var	int
	 */
	protected $crs_obj_id;

	/**
	 * @var \DateTime 
	 */
	protected $requested_ts;

	/**
 	 * @var \DateTime|null
	 */
	protected $finished_ts;

	public function __construct($id, $user_id, $session_id, $crs_obj_id, \DateTime $requested_ts, \DateTime $finished_ts = null) {
		assert('is_int($id)');
		assert('is_int($user_id)');
		assert('is_string($session_id)');
		assert('is_int($crs_obj_id)');
		$this->id = $id;
		$this->user_id = $user_id;
		$this->session_id = $session_id;
		$this->crs_obj_id = $crs_obj_id;
		$this->requested_ts = $requested_ts;
		$this->finished_ts = $finished_ts;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @return var 
	 */
	public function getSessionId() {
		return $this->session_id;
	}

	/**
	 * @return int
	 */
	public function getCourseObjId() {
		return $this->crs_obj_id;
	}

	/**
	 * @return \DateTime 
	 */
	public function getRequestedTS() {
		return $this->requested_ts;
	}

	/**
	 * @return \DateTime
	 */
	public function getFinishedTS() {
		return $this->finished_ts;
	}

	/**
	 * @return self
	 */
	public function withFinishedTS(\DateTime $finished_ts) {
		$clone = clone $this;
		$clone->finished_ts = $finished_ts;
		return $clone;
	}
}
