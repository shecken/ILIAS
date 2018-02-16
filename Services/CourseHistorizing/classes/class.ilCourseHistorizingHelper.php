<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseHistorizingHelper
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */


require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Modules/Course/classes/class.ilObjCourse.php");

class ilCourseHistorizingHelper 
{
	#region Singleton

	/** Defunct member for singleton */
	private function __clone() {}

	/** Defunct member for singleton */
	private function __construct() {}

	/** @var ilCourseHistorizingHelper $instance */
	private static $instance;

	/**
	 * Singleton accessor
	 * 
	 * @static
	 * 
	 * @return ilUserHistorizingHelper
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	#endregion

	/**
	 * Returns the template title of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getTemplateTitleOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getTemplateTitle();
	}

	/**
	 * Returns the type of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getTypeOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getType();
	}

	/**
	 * Returns the topic/s of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return array
	 */
	public static function getTopicOf(ilObjCourse $course)
	{
		$topic =  gevCourseUtils::getInstanceByObjOrId($course)
								->getTopics();
		if ($topic === null) {
			return array();
		}
		else {
			return $topic;
		}
	}

	/**
	 * Returns the begin of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return ilDate
	 */
	public static function getBeginOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getStartDate();
	}

	/**
	 * Returns the end of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return ilDate
	 */
	public static function getEndOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getEndDate();
	}

	/**
	 * Returns the hours of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return integer
	 */
	public static function getHoursOf(ilObjCourse $course)
	{
		// count hours in schedule 
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getAmountHours();
	}


	/**
	 * Returns the venue of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getVenueOf(ilObjCourse $course)
	{
		$venue = gevCourseUtils::getInstanceByObjOrId($course)
							 ->getVenueTitle();
		if ($venue == "") {
			return self::getVenueFreeTextOf($course);
		}

		return $venue;
	}

	/**
	 * Returns the accomodation of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getAccomodationOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getAccomodationTitle();
	}


	/**
	 * Returns the venue free text marker of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getVenueFreeTextOf(ilObjCourse $course)
	{
		$ret = gevCourseUtils::getInstanceByObjOrId($course)
							 ->getVenueFreeText();

		return ($ret) ? "FREITEXT" : "";
	}

	/**
	 * Returns the provider of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getProviderOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getProviderTitle();
	}

	/**
	 * Returns the max credit points of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getMaxCreditPointsOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getCreditPoints();
	}

	/**
	 * Returns the fee of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getFeeOf(ilObjCourse $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getFee();
	}

	/**
	 * Returns the tutor of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getTutorOf(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);

		$lastname = $utils->getMainTrainerLastname();
		$firstname = $utils->getMainTrainerFirstname();

		if ($lastname && $firstname) {
			return $lastname.", ".$firstname;
		}
	}
	
	/**
	 * Returns weather course is a template object or not.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getIsTemplate(ilObjCourse $course)
	{
		if (gevCourseUtils::getInstanceByObjOrId($course)
						  ->isTemplate()) {
			return "Ja";
		}
		else {
			return "Nein";
		}
	}

	/**
	 * Returns the standardized contents of the course for WBD
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */

	public static function getWBDTopicOf(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getWBDTopic();
	}

	public static function getEduProgramOf(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getEduProgramm();
	}

	public static function isOnline(ilObjCourse $course)
	{
		return $course->isActivated() ? 1 : 0;
	}

	public static function getDeadlineDataOf(ilObjCourse $course)
	{

		require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
		$mailings = new gevCrsAdditionalMailSettings($course->getId());
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		//get deadlines for course
		$ret = array(
			'dl_invitation'		=> $mailings->getInvitationMailingDate(),
			'dl_storno'			=> $utils->getCancelDeadline(),
			'dl_booking'		=> $utils->getBookingDeadline(),
			'dl_waitinglist'	=> $utils->getCancelWaitingList()
		);


		return $ret;


	}


	public static function getVirtualClassroomType(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getVirtualClassType();
	}

	public static function getDCTType(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		if($utils->isDecentralTraining()) {
			return ($utils->isFlexibleDecentrallTraining()) ? 'flexible' : 'fixed';
		}
		return;
	}

	public static function getTemplateObjId(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		$template_ref_id = $utils->getTemplateRefId();
		if($template_ref_id) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			return gevObjectUtils::getObjId($template_ref_id);
		}
		return;
	}

	public static function getIsCancelled(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getIsCancelled() ? 'Ja' : 'Nein';
	}

	public static function getSizeWaitingList(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getWaitingListLength();
	}

	public static function getMaxParticipants(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getMaxParticipants();
	}

	public static function getWaitinglistActive(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getWaitingListActive() ? 'Ja' : 'Nein';
	}
	
	public static function getMinParticipants(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getMinParticipants();
	}

	public static function getASTDCategory(ilObjCourse $course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $utils->getASTDCategory();
	}
}