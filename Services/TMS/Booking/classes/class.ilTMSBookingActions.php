<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

/**
 * This is how the booking of users really works. Dark magic happening here.
 */
class ilTMSBookingActions implements Booking\Actions {
	/**
	 * Book the given user on the course. 
	 *
	 * @param	int		$crs_ref_id
	 * @param	int		$user_id
	 */
	public function bookUser($crs_ref_id, $user_id) {
		$course = ilObjectFactory::getInstanceByRefId($crs_ref_id);
		assert('$course instanceof \ilObjCourse');
		$user = ilObjectFactory::getInstanceByObjId($user_id);
		assert('$user instanceof \ilObjUser');

		$this->maybeMakeCourseMember($course, $user);
	}

	/**
	 * Make the user be a member of the course, if he does not already have a member role.
	 *
	 * @throws	\LogicException if user does have another role on the course than member
	 * @throws	\LogicException if user could not booked or added to waitinglist
	 * @param	\ilObjCourse    $course
	 * @param	\ilObjUser      $user
	 * @return	void
	 */
	protected function maybeMakeCourseMember(\ilObjCourse $course, \ilObjUser $user) {
		require_once("Modules/Course/classes/class.ilCourseParticipant.php");
		$participant = \ilCourseParticipant::_getInstancebyObjId($course->getId(), $user->getId());
		if ($participant->isMember()) {
			return;
		}
		if ($participant->isParticipant()) {
			throw new \LogicException("User already has a local role on the course. Won't be able to make him a member.");
		}

		$booking_modality = $this->getFirstBookingModalities((int)$course->getRefId());

		if($booking_modality) {
			require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
			$booked = false;
			if($this->maybeBookAsMember((int)$course->getRefId(), $booking_modality)) {
				$participant->add($user->getId(), IL_CRS_MEMBER);
				$booked = true;
			}

			if($booked == false && $this->maybeAddOnWaitingList($course, $booking_modality)) {
				$course->waiting_list_obj->addToList((int)$user->getId());
			}
		}

		throw new \LogicException("User can not be booked. Course and waitinglist are overbooked");
	}

	/**
	 * Check user can be booked as member
	 *
	 * @param int 	$crs_ref_id
	 * @param $booking_modality
	 *
	 * @return bool
	 */
	protected function maybeBookAsMember($crs_ref_id, \ilObjBookingModalities $booking_modality) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		$max_member = $booking_modality->getBooking()->max();
		$current_member = \ilCourseParticipants::lookupNumberOfMembers($crs_ref_id);

		if($max_member === null || $current_member < $max_member){
			return true;
		}

		return false;
	}

	/**
	 * Checks user can be added to waitinglist
	 *
	 * @param \ilObjCourse 	$course
	 * @param $booking_modality
	 *
	 * @return bool
	 */
	protected function maybeAddOnWaitingList(\ilObjCourse $course, \ilObjBookingModalities $booking_modality) {
		$course->initWaitingList();
		$max_waiting = $booking_modality->getWaiting()->max();
		$current_waiting = $course->waiting_list_obj->getCountUsers();

		if($max_waiting === null || $current_waiting < $max_waiting) {
			return true;
		}

		return false;
	}

	/**
	 * Get the first booking modalities below crs
	 *
	 * @param int 	$crs_ref_id
	 *
	 * @return BookingModalities | null
	 */
	protected function getFirstBookingModalities($crs_ref_id) {
		global $DIC;
		$g_tree = $DIC->repositoryTree();
		$booking_modalities = $g_tree->getChildsByType($crs_ref_id, "xbkm");
		if(count($booking_modalities) > 0) {
			$booking_modality = $booking_modalities[0];
			return ilObjectFactory::getInstanceByRefId($booking_modality["child"]);
		}

		return null;
	}
}

/**
 * cat-tms-patch end
 */
