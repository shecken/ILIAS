<?php

class Helper {
	const UNLIMITED_MEMBER_SPOTS = "∞";

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	public function __construct() {
		global $DIC;
		$this->g_user = $DIC->user();
	}

	/**
	 * Get needed values from bkm. Just best for user
	 *
	 * @param ilObjBookingModalities[] 	$bms
	 * @param ilDateTime 	$crs_start_date
	 *
	 * @return array<intger, int | ilDateTime | string>
	 */
	public function getBestBkmValues(array $bkms, ilDateTime $crs_start_date) {
		$first = array_shift($bkms);
		$max_member = $first->getMember()->getMax();
		$min_member = $first->getMember()->getMin();
		$booking_start = $first->getBooking()->getBeginning();
		$booking_end = $first->getBooking()->getDeadline();
		$waiting_list = $first->getWaitinglist()->getModus();

		foreach ($bkms as $key => $bkm) {
			$max_member = $bkm->getMember()->compareMax($max_member);
			$min_member = $bkm->getMember()->compareMin($min_member);
			$booking_start = $bkm->getBooking()->compareBebeginning($booking_start);
			$booking_end = $bkm->getBooking()->compareDeadline($booking_end);
			$waiting_list = $first->getWaitinglist()->compareWaitingList($waiting_list);
		}

		$booking_start_date = clone $crs_start_date;
		$booking_start_date->increment(ilDateTime::DAY, -1 * $booking_start);

		$booking_end_date = clone $crs_start_date;
		$booking_end_date->increment(ilDateTime::DAY, -1 * $booking_end);

		$bookings_available = self::UNLIMITED_MEMBER_SPOTS;
		if($max_member) {
			$bookings_available = $max_member - $crs_member_count;
		}

		return array($max_member, $booking_start_date, $booking_end_date, $waiting_list, $min_member);
	}

	/**
	 * Get information about selected venue
	 *
	 * @param int 	$crs_id
	 *
	 * @return string[]
	 */
	public function getVenueInfos($crs_id) {
		$vplug = ilPluginAdmin::getPluginObjectById('venues');
		$vactions = $vplug->getActions();
		$vassignment = $vactions->getAssignment((int)$crs_id);

		if($vassignment) {
			if($vassignment->isCustomAssignment()) {
				$venue_id = -1;
				$city = $vassignment->getVenueText();
				$address = "";
			}

			if($vassignment->isListAssignment()) {
				$venue_id = $vassignment->getVenueId();
				$venue = $vactions->getVenue($venue_id);
				$city = $venue->getAddress()->getCity();
				$address = $venue->getAddress()->getAddress1();
			}
		}

		return array($venue_id, $city, $address);
	}

	/**
	 * Get information about selected provider
	 *
	 * @param int 	$crs_id
	 *
	 * @return string[]
	 */
	public function getProviderInfos($crs_id) {
		$vplug = ilPluginAdmin::getPluginObjectById('trainingprovider');
		$pactions = $vplug->getActions();
		$passignment = $pactions->getAssignment((int)$crs_id);
		$provider_id = -1;

		if($passignment) {
			if($passignment->isListAssignment()) {
				$provider_id = $passignment->getProviderId();
			}
		}

		return array($provider_id);
	}

	/**
	 * Get information from course classification object
	 *
	 * @param ilObjCourseClassification 	$ccl
	 *
	 * @return array<integer, string | int[] | string[] | null> 
	 */
	public function getCourseClassificationValues($ccl) {
		if($ccl === null) {
			return array(null,
				"",
				array(),
				array(),
				"",
				array(),
				array(),
			);
		}

		$actions = $ccl->getActions();
		$settings = $ccl->getCourseClassification();

		$target_group = array();
		$topics = array();
		$type = "";

		$target_group_ids = $settings->getTargetGroup();
		if($target_group_ids !== null) {
			$target_group = $actions->getTargetGroupNames($target_group_ids);
		}

		$topic_ids = $settings->getTopics();
		if($topic_ids !== null) {
			$topics = $actions->getTopicsNames($topic_ids);
		}

		$type_id = $settings->getType();
		if($type_id !== null) {
			$type = array_shift($actions->getTypeName($type_id));
		}

		return array($type_id,
			$type,
			$target_group_ids,
			$target_group,
			(string)$settings->getGoals(),
			$topic_ids,
			$topics
		);
	}

	/**
	 * Form date for gui as user timezone string
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	public function formatDate($dat, $use_time=false) {
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $this->g_user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}
}