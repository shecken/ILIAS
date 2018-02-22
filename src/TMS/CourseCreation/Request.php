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
	protected $crs_ref_id;

	/**
	 * @var \DateTime 
	 */
	protected $requested_ts;

	/**
 	 * @var \DateTime|null
	 */
	protected $finished_ts;

	/**
 	 * @var int		$id
 	 * @var int		$user_id
 	 * @var string	$session_id
 	 * @var int		$crs_ref_id
	 */
	public function __construct($id, $user_id, $session_id, $crs_ref_id, \DateTime $requested_ts, \DateTime $finished_ts = null) {
		assert('is_int($id)');
		assert('is_int($user_id)');
		assert('is_string($session_id)');
		assert('is_int($crs_ref_id)');
		$this->id = $id;
		$this->user_id = $user_id;
		$this->session_id = $session_id;
		$this->crs_ref_id = $crs_ref_id;
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
	public function getCourseRefId() {
		return $this->crs_ref_id;
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
