<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Build requests from data given to it.
 */
interface RequestBuilder {
	/**
	 * Set the user and session id for the requests.
	 *
	 * @var	int		$user_id
	 * @var string	$session_id
	 * @return	self
	 */
	public function setUserIdAndSessionId($user_id, $session_id);

	/**
	 * Set the course id for the request.
	 *
	 * @var	int		$crs_ref_id
	 * @return	self
	 */
	public function setCourseRefId($crs_ref_id);

	/**
	 * Get the request object requested as of given timestamp.
	 *
	 * @var	\DateTime	$requested_ts
	 * @return	Request
	 */
	public function getRequest();
}
