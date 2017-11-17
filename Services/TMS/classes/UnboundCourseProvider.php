<?php

use \CaT\Ente\ILIAS\UnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;

class UnboundCourseProvider extends Base {
	/**
	 * @inheritdocs
	 */
	public function componentTypes() {
		return [CourseInfo::class];
	}

	/**
	 * Build the component(s) of the given type for the given object.
	 *
	 * @param   string    $component_type
	 * @param   Entity    $provider
	 * @return  Component[]
	 */
	public function buildComponentsOf($component_type, Entity $entity) {
		global $DIC;
		$this->lng = $DIC["lng"];
		$this->lng->loadLanguageModule("tms");
		$this->lng->loadLanguageModule("crs");
		$this->user = $DIC->user();
		$this->object = $entity->object();

		if ($component_type === CourseInfo::class) {
			$ret = array();

			$ret[] = $this->getCourseTitle();
			$ret = $this->getCoursePeriod($ret);
			$ret = $this->getBookingStatus($ret);

			require_once("Modules/Course/classes/class.ilCourseParticipants.php");
			require_once("Services/Membership/classes/class.ilWaitingList.php");
			if(\ilCourseParticipants::_isParticipant($object->getRefId(), $user->getId())) {
				$ret[] = $this->createCourseInfoObject($entity
						, $lng->txt("status")
						, $lng->txt("booked_as_member")
						, 600
						, [
							CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
						  ]
					);
			}

			if(\ilWaitingList::_isOnList($user->getId(), $object->getId())) {
				$ret[] = $this->createCourseInfoObject($entity
						, $lng->txt("status")
						, $lng->txt("booked_on_waitinglist")
						, 600
						, [
							CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
						  ]
					);
			}

			$venue_components = $this->getVenueComponents($entity, (int)$object->getId());
			$ret = array_merge($ret, $venue_components);
			$training_provider_components = $this->getTrainingProviderComponents($entity, (int)$object->getId());
			$ret = array_merge($ret, $training_provider_components);

			$crs_important_info = nl2br(trim($object->getImportantInformation()));
			if($crs_important_info != "") {
				$ret[] = $this->createCourseInfoObject($entity
						, $lng->txt("crs_important_info")
						, $crs_important_info
						, 1000
						, [
							CourseInfo::CONTEXT_SEARCH_DETAIL_INFO,
							CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
							CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO
						  ]
					);

				$ret[] = $this->createCourseInfoObject($entity
						, $lng->txt("crs_important_info")
						, $crs_important_info
						, 700
						, [
							CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO
						  ]
					);
			}

			$tutor_ids = $object->getMembersObject()->getTutors();
			if(count($tutor_ids) > 0) {
				foreach ($tutor_ids as $tutor_id) {
					$tutor_names[] = \ilObjUser::_lookupFullname($tutor_id);
				}

				$tutor_names = join(", ", $tutor_names);
				$ret[] = $this->createCourseInfoObject($entity
						, $lng->txt("trainer")
						, $tutor_names
						, 1300
						, [
							CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO
						  ]
					);
			}

			$sessions = $this->getSessionsOfCourse($object->getRefId());
			if(count($sessions) > 0) {
				foreach ($sessions as $session) {
					$appointment 	= $session->getFirstAppointment();
					$start_time 	= $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i", "UTC");
					$end_time 		= $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i", "UTC");
					$offset 		= $appointment->getDaysOffset();

					$vals[$offset] = $lng->txt("day")." ".$offset." ".$start_time." - ".$end_time;
				}

				asort($vals);
				$vals = join("<br />", $vals);

				$ret[] = $this->createCourseInfoObject($entity
						, ""
						, $vals
						, 1000
						, [
							CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO
						  ]
					);
			}


			return $ret;
		}
		throw new \InvalidArgumentException("Unexpected component type '$component_type'");
	}

	protected function getCourseTitle() {
		return $this->createCourseInfoObject($this->entity
				, $this->lng->txt("title")
				, $this->object->getTitle()
				, 100
				, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
					CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
					CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
				  ]
			);
	}

	protected function getCoursePeriod($ret) {
		$crs_start = $this->object->getCourseStart();
		if($crs_start === null) {
			return $ret;
		}

		$date = $this->formatPeriod($crs_start, $this->object->getCourseEnd());
		$ret[] = $this->createCourseInfoObject($this->entity
			, $this->lng->txt("date")
			, $date
			, 300
			, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
				CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
				CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
				CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO,
				CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
			  ]
		);

		$ret[] = $this->createCourseInfoObject($this->entity
			, $this->lng->txt("date")
			, $date
			, 900
			, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
		);

		return $ret;
	}

	/**
	 * Find sessions underneath course 
	 *
	 * @param 	int 			$crs_ref_id
	 * @return 	ilObjSession[]
	 */
	protected function getSessionsOfCourse($crs_ref_id)
	{
		global $DIC;

		$g_tree 	= $DIC->repositoryTree();
		$ret 		= array();
		$sessions 	= $g_tree->getChildsByType($crs_ref_id, "sess");

		foreach($sessions as $session)
		{
			$ret[] = ilObjectFactory::getInstanceByRefId($session['ref_id']);
		}

		return $ret;
	}

	/**
	 * Form date.
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatDate(\ilDateTime $date) {
		global $DIC;
		$g_user = $DIC->user();
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $date->get(IL_CAL_FKT_DATE, $out_format, $g_user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}

	/**
	 * Form date period.
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatPeriod(\ilDateTime $date1, \ilDateTime $date2) {
		return $this->formatDate($date1)." - ".$this->formatDate($date2);
	}

	/**
	 * Checks venue plugin is aktive and returns component objects
	 *
	 * @param int 	$crs_id
	 *
	 * @return CourseInfoImpl[]
	 */
	protected function getVenueComponents(Entity $entity, $crs_id) {
		assert('is_int($crs_id)');
		$ret = array();
		if(ilPluginAdmin::isPluginActive('venues')) {
			$vplug = ilPluginAdmin::getPluginObjectById('venues');
			$txt = $vplug->txtClosure();
			list($venue_id, $city, $address, $name, $postcode) = $vplug->getVenueInfos($crs_id);

			if($city != "") {
				$ret[] = $this->createCourseInfoObject($entity
				, ""
				, $city
				, 400
				, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
					CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
				  ]
				);
			}

			if($name != "") {
				$ret[] = $this->createCourseInfoObject($entity
					, $txt("title")
					, $name
					, 350
					, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
					);

				$ret[] = $this->createCourseInfoObject($entity
					, $txt("title")
					, $name
					, 1200
					, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
					);
			}

			if($address != "") {
				$ret[] =  $this->createCourseInfoObject($entity
					, $txt("address")
					, $address
					, 360
					, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
					);

				$ret[] = $this->createCourseInfoObject($entity
					, ""
					, $address
					, 1300
					, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
					);
			}

			if($postcode != "" || $city != "") {
				$ret[] = $this->createCourseInfoObject($entity
					, ""
					, $postcode." ".$city
					, 370
					, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
					);

				$ret[] = $this->createCourseInfoObject($entity
					, ""
					, $postcode." ".$city
					, 1400
					, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
					);
			}
		}
		return $ret;
	}

	/**
	 * Checks training provider plugin is activ and returns component objects
	 *
	 * @param int 	$crs_id
	 *
	 * @return CourseInfoImpl[]
	 */
	protected function getTrainingProviderComponents(Entity $entity, $crs_id) {
		assert('is_int($crs_id)');
		$ret = array();
		if(ilPluginAdmin::isPluginActive('trainingprovider')) {
			$vplug = ilPluginAdmin::getPluginObjectById('trainingprovider');
			$txt = $vplug->txtClosure();
			list($provider_id, $provider) = $vplug->getProviderInfos($crs_id);

			if($provider != "") {
				$ret[] = $this->createCourseInfoObject($entity
					, $txt("title")
					, $provider
					, 1100
					, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
					);
			}
		}
		return $ret;
	}

	/**
	 * Create a course inforamtion object
	 *
	 * @param Entity 	$entity
	 * @param string 	$label
	 * @param int | array | string 	$value
	 * @param int 	$step
	 * @param string[] 	$contexte
	 *
	 * @return CourseInfoImpl
	 */
	protected function createCourseInfoObject(Entity $entity, $label, $value, $step, $contexts) {
		return new CourseInfoImpl
					( $entity
					, $label
					, $value
					, ""
					, $step
					, $contexts
				);
	}
}
