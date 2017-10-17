<?php

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * cat-tms-patch start
 */

class BookableCourse {


	/**
	 * @var	int
	 */
	protected $ref_id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var ilDateTime
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
	 * @var ilDateTime
	 */
	protected $end_date;

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

	public function __construct
		($ref_id,
		$title,
		$type,
		ilDateTime $begin_date,
		$bookings_available,
		array $target_group,
		$goals,
		array $topics,
		ilDateTime $end_date,
		$location,
		$address,
		$fee
	) {
		assert('is_int($ref_id)');
		assert('is_string($title)');
		assert('is_string($type)');
		assert('is_string($bookings_available)');
		assert('is_array($target_group)');
		assert('is_string($goals)');
		assert('is_array($topics)');
		assert('is_string($location)');
		assert('is_string($address)');
		assert('is_string($fee)');

		$this->ref_id = $ref_id;
		$this->title = $title;
		$this->type = $type;
		$this->begin_date = $begin_date;
		$this->bookings_available = $bookings_available;
		$this->target_group = $target_group;
		$this->goals = $goals;
		$this->topics = $topics;
		$this->end_date = $end_date;
		$this->location = $location;
		$this->address = $address;
		$this->fee = $fee;
	}

	public function getRefId() {
		return $this->ref_id;
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

	public function getEndDate() {
		return $this->end_date;
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

	// TODO: this propably doesn't belong here. This might be removed or consolidated
	// once the search logic is turned into a proper db-query.

	use ilHandlerObjectHelper;

	protected function getDIC() {
		return $GLOBALS["DIC"];
	}

	protected function getEntityRefId() {
		return $this->ref_id;
	}

	public function getTitleValue() {
		return $this->getTitle();
	}

	public function getSubTitleValue() {
		return $this->getType();
	}

	public function getImportantFields() {
		global $DIC;
		$lng = $DIC["lng"];
		return
			[ $this->formatDate($this->getBeginDate())
			, $this->getLocation()
			, $lng->txt("available_slots") => $this->getBookingsAvailable()
			];
	}

	public function getFurtherFields() {
		global $DIC;
		$lng = $DIC["lng"];
		return
			[ $lng->txt("location") => $this->getLocation()
			, $this->getAddress()
			, $lng->txt("date") => $this->formatDate($this->getBeginDate())." - ".$this->formatDate($this->getEndDate())
			, $lng->txt("available_slots") => $this->getBookingsAvailable()
			, $lng->txt("fee") => $this->getFee()
			];
	}

	public function getDetailFields() {
		global $DIC;
		$lng = $DIC["lng"];
		$ui_factory = $DIC->ui()->factory();
		return
			[ $lng->txt("target_groups") => $ui_factory->listing()->unordered($this->getTargetGroup())
			, $lng->txt("goals") => $this->getGoals()
			, $lng->txt("topics") => $ui_factory->listing()->unordered($this->getTopics())
			];
	}

	/**
	 * Form date for gui as user timezone string
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatDate($dat, $use_time = false) {
		global $DIC;
		$user = $DIC["ilUser"];
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}
}

/**
 * cat-tms-patch end
 */
