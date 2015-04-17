<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevBookingGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->current_user = &$ilUser;
		$this->user_id = null;
		$this->user_utils = null;
		$this->crs_id = null;
		$this->crs_utils = null;
		$this->is_self_learning = null;
		$this->is_webinar = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->initUser();
		$this->initCourse();

		$this->checkIfCourseIsOnlineAndBookable();
		$this->checkIfUserAlreadyBookedASimilarCourse();
		$this->checkIfCourseIsFull();
		$this->checkIfUserIsAllowedToBookCourseForOtherUser();
		$this->checkIfUserIsAllowedToBookCourse();
		$this->checkOtherBookingsInPeriod();
		
		$this->cmd = $this->ctrl->getCmd();
		
		switch($this->cmd) {
			case "backToSearch":
				$this->toCourseSearch();
				break;
			case "book":
			case "finalizeBooking":
				$this->setRequestParameters();
				$cmd = $this->cmd;
				$cont = $this->$cmd();
				break;
			default:
				$this->log->write("gevBookingGUI: Unknown command '".$this->cmd."'");
				throw new ilException("Unknown command: ".$this->cmd);
		}
		
		
		if ($cont) {
			$this->insertInTemplate($cont, $this->cmd);
		}
	}
	
	protected function initUser() {
		if ($_GET["user_id"] === null) {
			$this->log->write("gevBookingGUI::initUser: No user id in GET.");
			ilUtil::redirect("index.php");
		}
		
		$this->user_id = $_GET["user_id"];
		$this->user_utils = gevUserUtils::getInstance($this->user_id);
	}
	
	protected function initCourse() {
		if ($_GET["crs_id"] === null) {
			$this->log->write("gevBookingGUI::initCourse: No course id in GET.");
			ilUtil::redirect("index.php");
		}
		
		$this->crs_id = $_GET["crs_id"];
		$this->crs_utils = gevCourseUtils::getInstance($this->crs_id);
	}
	
	protected function toCourseSearch() {
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmd=toCourseSearch");
		exit();
	}
	
	protected function checkIfCourseIsOnlineAndBookable() {
		if (   $this->crs_utils->getCourse()->getOfflineStatus()
			|| $this->crs_utils->isBookingDeadlineExpired()) {
			ilUtil::sendFailure( $this->lng->txt("gev_course_expired")
							   , true);
			$this->toCourseSearch();
		}
	}
	
	protected function checkIfUserAlreadyBookedASimilarCourse() {
		if (!$this->user_utils->canBookCourseDerivedFromTemplate($this->crs_utils->getTemplateRefId())) {
			ilUtil::sendFailure( $this->isSelfBooking() ? $this->lng->txt("gev_booked_similar_course_self")
														: $this->lng->txt("gev_booked_similar_course_employee")
							   , true);
			$this->toCourseSearch();
		}
	}
	
	protected function checkIfCourseIsFull() {
		$free_places = $this->crs_utils->getFreePlaces();
		if ( $free_places && $free_places <= 0 
		  && !$this->crs_utils->isWaitingListActivated()) {
			ilUtil::sendFailure($this->lng->txt("gev_course_is_full"), true);
			$this->toCourseSearch();
		}
	}
	
	protected function checkIfUserIsAllowedToBookCourseForOtherUser() {
		if ( !$this->crs_utils->canBookCourseForOther($this->current_user->getId(), $this->user_id)) {
			if ($this->current_user->getId() == $this->user_id) {
				ilUtil::sendFailure($this->lng->txt("gev_not_allowed_to_book_crs_for_self"), true);
			}
			else {
				ilUtil::sendFailure($this->lng->txt("gev_not_allowed_to_book_crs_for_other"), true);
			}
			$this->toCourseSearch();
		}
	}
	
	public function checkIfUserIsAllowedToBookCourse() {
		if ( !$this->crs_utils->isBookableFor($this->user_id)) {
			ilUtil::sendFailure($this->lng->txt("gev_user_not_allowed_to_book_crs"), true);
			$this->toCourseSearch();
		}
	}
	
	public function checkOtherBookingsInPeriod() {
		$start = $this->crs_utils->getStartDate();
		$end = $this->crs_utils->getEndDate();
		
		if ($start === null || $end === null) {
			return;
		}
		
		require_once("Services/CourseBooking/classes/class.ilUserCourseBookings.php");
		$others = ilUserCourseBookings::getInstance($this->user_id)
									  ->getCoursesDuring($start, $end);
		if (count($others) == 0) {
			return;
		}
		
		if ($this->current_user->getId() == $this->user_id) {
			ilUtil::sendFailure($this->lng->txt("gev_not_allowed_to_book_crs_self_because_period"), true);
		}
		else {
			ilUtil::sendFailure($this->lng->txt("gev_not_allowed_to_book_crs_for_other_because_period"), true);
		}
		$this->toCourseSearch();
		
		/*require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		
		if ($this->isSelfBooking()) {
			$msg = $this->lng->txt("gev_booking_other_courses_in_period_self")."<br />";
		}
		else {
			$msg = $this->lng->txt("gev_booking_other_courses_in_period_others")."<br />";
		}
		foreach($others as $crs) {
			$msg .= $crs["title"]." (".ilDatePresentation::formatPeriod($crs["start"], $crs["end"]).")</br>";
		}
		ilUtil::sendInfo($msg);*/
	}
	
	protected function setRequestParameters() {
		$this->ctrl->setParameter($this, "crs_id", $this->crs_id);
		$this->ctrl->setParameter($this, "user_id", $this->user_id);
	}
	
	protected function isSelfBooking() {
		return $this->user_id == $this->current_user->getId();
	}
	
	protected function isSelfLearningCourse() {
		if ($this->is_self_learning === null) {
			$this->is_self_learning = $this->crs_utils->getType() == gevSettings::ONLINE_TRAINING;
		}
		return $this->is_self_learning;
	}
	
	protected function isWebinar() {
		if ($this->is_webinar === null) {
			$this->is_webinar = $this->crs_utils->getType() == gevSettings::WEBINAR;
		}
		return $this->is_webinar;
	}
	
	protected function insertInTemplate($a_cont, $a_cmd) {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
		
		if ($this->isSelfBooking()) {
			$title = new catTitleGUI("gev_booking", "gev_booking_header_note", "GEV_img/ico-head-booking.png");
			$employee = "";
		}
		else {
			// TODO: correct textes here.
			require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
			require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
			
			$title = new catTitleGUI("gev_book_employee", "gev_booking_header_note", "GEV_img/ico-head-booking.png");
			$spacer = new catHSpacerGUI();
			
			$form = new catPropertyFormGUI();
			$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
			$field = new ilNonEditableValueGUI($this->lng->txt("gev_booking_for"), "", true);
			$field->setValue($this->user_utils->getFullName());
			$form->addItem($field);
			
			$employee = $spacer->render()
					  . $form->getContent()
					  . $spacer->render()
					  ;
		}
		
		$this->tpl->setContent( $title->render()
							  . $employee
							  . $a_cont
							  );
	}
	
	protected function book() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->crs_utils->getTitle());
		$form->addCommandButton("backToSearch", $this->lng->txt("gev_to_course_search"));
		
		$form->addCommandButton("finalizeBooking", $this->lng->txt("gev_obligatory_booking"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$prv = $this->crs_utils->getProvider();
		$ven = $this->crs_utils->getVenue();
		$booking_dl = $this->crs_utils->getFormattedBookingDeadlineDate();
		$officer_contact = $this->crs_utils->getTrainingOfficerContactInfo();
		$desc = $this->crs_utils->getSubtitle();
		$appointment = $this->crs_utils->getFormattedAppointment();
		
		$vals = array(
			  array($this->lng->txt("description")
				   , $desc
				   , $desc
				   )
			, array( $this->lng->txt("gev_course_id")
				   , true
				   , $this->crs_utils->getCustomId()
				   )
			, array( $this->lng->txt("gev_target_group")
				   , true
				   , $this->crs_utils->getTargetGroupDesc()
				   )
			, array( $this->lng->txt("gev_targets_and_benefit")
				   , true
				   , $this->crs_utils->getGoals()
				   )
			, array( $this->lng->txt("gev_contents")
				   , true
				   , $this->crs_utils->getContents()
				   )
			, array( $this->lng->txt("gev_course_type")
				   , true
				   , implode(", ", $this->crs_utils->getType())
				   )
			, array( $this->lng->txt("appointment")
				   , $appointment
				   , $appointment."<br/><br />".$this->crs_utils->getFormattedSchedule()
				   )
			, array( $this->lng->txt("gev_provider")
				   , $prv?true:false
				   , $prv?$prv->getTitle():""
				   )
			, array( $this->lng->txt("gev_venue")
				   , $ven?true:false
				   , !$ven?"":( $ven->getTitle()."<br />".
				   				$ven->getStreet()." ".$ven->getHouseNumber()."<br />".
				   				$ven->getZipcode()." ".$ven->getCity()."<br />"
				   			  )
				   )
			, array( $this->lng->txt("gev_instructor")
				   , true
				   , $this->crs_utils->getMainTrainerName()
				   )
			, array( $this->lng->txt("gev_subscription_end")
				   , $booking_dl != "" && !$this->isSelfLearningCourse()
				   , $this->lng->txt("until") . " ". $this->crs_utils->getFormattedBookingDeadlineDate()
				   )

			, array( $this->lng->txt("gev_free_cancellation_until")
				   , $booking_dl != "" && !$this->isSelfLearningCourse()
				   , $this->lng->txt("until") . " ". $this->crs_utils->getFormattedCancelDeadlineDate()
				   )

			, array( $this->lng->txt("gev_free_places")
				   , !$this->isSelfLearningCourse()
				   , $this->crs_utils->getFreePlaces()
				   )
			, array( $this->lng->txt("gev_training_contact")
				   , !$this->isSelfLearningCourse() && $officer_contact
				   , $officer_contact
				   )
			//, array( $this->lng->txt("precondition")
			//	   , true
			//	   , $this->crs_utils->getFormattedPreconditions()
			//	   )
			);
		
		foreach ($vals as $val) {
			if (!$val[1] or !$val[2]) {
				continue;
			}
		
			$field = new ilNonEditableValueGUI($val[0], "", true);
			$field->setValue($val[2]);
			$form->addItem($field);
		}
		
		if ($this->crs_utils->isWithAccomodations()) {
			$this->lng->loadLanguageModule("acco");
			ilSetAccomodationsGUI::addAccomodationsToForm($form, $this->crs_id, $this->user_id, "acco", true);
			if ($_POST["acco"]) {
				$form->getItemByPostVar("acco")->setValue($_POST["acco"]);
			}
			else if ($_POST["accomodations"]) {
				$form->getItemByPostVar("acco")->setValue(unserialize($_POST["accomodations"]));
			}
		}
		
		return $form->getHTML();
	}
	
	private function getAccomodationsForm() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		$_form = new catPropertyFormGUI();
		ilSetAccomodationsGUI::addAccomodationsToForm($_form, $this->crs_id, $this->user_id);
		return $_form;
	}
	protected function finalizeBooking() {
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		
		if ($this->crs_utils->isWithAccomodations()) {
			$_form = $this->getAccomodationsForm();
			if (!$_form->checkInput()) {
				$this->log->write("gevBookingGUI::finalizeBooking: This should not happen, the form input did not check correctly.");
				$this->toCourseSearch();
				return;
			}
			$accomodations = $_form->getInput("acco");
		}
		else {
			$accomodations = null;
		}

		if (!$this->crs_utils->bookUser($this->user_id)) {
			$this->failAtFinalize("Someone managed to get here but not being able to book the course.");
		}
		if ($a_accomodations) {
			$acco = ilSetAccomodationsGUI::formInputToAccomodationsArray($a_accomodations);	
			$acco_inst = ilAccomodations::getInstance($this->crs_utils->getCourse());
			$acco_inst->setAccomodationsOfUser($this->user_id, $acco);
		}
		
		$status = $this->crs_utils->getBookingStatusOf($this->user_id);
		
		if ($status != ilCourseBooking::STATUS_BOOKED && $status != ilCourseBooking::STATUS_WAITING) {
			$this->failAtFinalize("Status was neither booked nor waiting.");
		}
		
		$this->finalizedBookingRedirect($status);
	}
	
	protected function failAtFinalize($msg) {
		$this->log->write("gevBookingGUI::finalizeBooking: ".$msg);
		ilUtil::sendFailure($this->lng->txt("gev_finalize_booking_error"), true);
		$this->toCourseSearch();
		exit();
	}
	
	protected function finalizedBookingRedirect($a_status) {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$booked = $a_status == ilCourseBooking::STATUS_BOOKED;
		$automails = new gevCrsAutoMails($this->crs_id);
		
		if ($this->isSelfBooking()) {
			if (!$this->isSelfLearningCourse()) {
				if ($booked) {
					$automails->send("self_booking_to_booked", array($this->user_id));
					$automails->send("invitation", array($this->user_id));
				}
				else {
					$automails->send("self_booking_to_waiting", array($this->user_id));
				}
			}
			
			ilUtil::sendSuccess( sprintf( $booked ? $this->lng->txt("gev_was_booked_self")
												  : $this->lng->txt("gev_was_booked_waiting_self")
										, $this->crs_utils->getTitle()
										)
								, true
								);
			
			ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
		}
		else {
			if (!$this->isSelfLearningCourse()) {
				if ($booked) {
					$automails->send("superior_booking_to_booked", array($this->user_id));
					$automails->send("invitation", array($this->user_id));
				}
				else {
					$automails->send("superior_booking_to_waiting", array($this->user_id));
				}
			}
			
			ilUtil::sendSuccess( sprintf ($booked ? $this->lng->txt("gev_was_booked_employee")
										 		  : $this->lng->txt("gev_was_booked_waiting_employee")
										 , $this->user_utils->getFirstname()." ".$this->user_utils->getLastname()
										 , $this->crs_utils->getTitle()
										 )
								, true
								);
			$this->toCourseSearch();
		}
	}
}

?>