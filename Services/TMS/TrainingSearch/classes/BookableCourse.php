<?php

/**
 * cat-tms-patch start
 */

class BookableCourse {
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $begin_date;

	/**
	 * @var string
	 */
	protected $bookings_available;

	/**
	 * @var string[]
	 */
	protected $target_group;

	/**
	 * @var string
	 */
	protected $goals;

	/**
	 * @var string[]
	 */
	protected $topics;

	/**
	 * @var string
	 */
	protected $date;

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $address;

	/**
	 * @var string
	 */
	protected $fee;

	public function __construct($title,
		$type,
		$begin_date,
		$bookings_available,
		array $target_group,
		$goals,
		array $topics,
		$date,
		$location,
		$address,
		$fee
	) {
		assert('is_string($title)');
		assert('is_string($type)');
		assert('is_string($begin_date)');
		assert('is_string($bookings_available)');
		assert('is_array($target_group)');
		assert('is_string($goals)');
		assert('is_array($topics)');
		assert('is_string($date)');
		assert('is_string($location)');
		assert('is_string($address)');
		assert('is_string($fee)');

		$this->title = $title;
		$this->type = $type;
		$this->begin_date = $begin_date;
		$this->bookings_available = $bookings_available;
		$this->target_group = $target_group;
		$this->goals = $goals;
		$this->topics = $topics;
		$this->date = $date;
		$this->location = $location;
		$this->address = $address;
		$this->fee = $fee;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getType() {
		return $this->type;
	}

	public function getBeginDate() {
		return $this->begin_date;
	}

	public function getBookingsAvailable() {
		return $this->bookings_available;
	}

	public function getTargetGroup() {
		return $this->target_group;
	}

	public function getGoals() {
		return $this->goals;
	}

	public function getTopics() {
		return $this->topics;
	}

	public function getDate() {
		return $this->date;
	}

	public function getLocation() {
		return $this->location;
	}

	public function getAddress() {
		return $this->address;
	}

	public function getFee() {
		return $this->fee;
	}
}

/**
 * cat-tms-patch end
 */