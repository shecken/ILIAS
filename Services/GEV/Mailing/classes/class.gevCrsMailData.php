<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

/**
 * Generali mail data for courses
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

class gevCrsMailData extends ilMailData {
	protected $rec_email;
	protected $rec_fullname;
	protected $rec_user_id;
	protected $crs_utils;
	protected $usr_utils;
	protected $cache;
	
	public function __construct() {
		$this->crs_utils = null;
		$this->usr_utils = null;
	}
	
	function getRecipientMailAddress() {
		return $this->rec_email;
	}
	function getRecipientFullName() {
		return $this->rec_fullname;
	}
	
	function hasCarbonCopyRecipients() {
		return false;
	}
	
	function getCarbonCopyRecipients() {
		return array();
	}
	
	function hasBlindCarbonCopyRecipients() {
		return false;
	}
	
	function getBlindCarbonCopyRecipients() {
		return array();
	}
	
	function maybeFormatEmptyField($val) {
		if ($val === null) {
			return "-";
		}
		else {
			return $val;
		}
	}
	
	function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false) {
		if (  $this->crs_utils === null) {
			throw new Exception("gevCrsMailData::getPlaceholderLocalized: course utilities not initialized.");
		}
		
		$val = null;
		global $lng;
		
		switch ($a_placeholder_code) {
			case "MOBIL":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getMobilePhone();
				}
				break;
			case "TITLE":
				$val = $this->crs_utils->getTitle();
				break;
			case "SUBTITLE":
				$val = $this->crs_utils->getSubtitle();
				break;
			case "TYPE":
				$val = $this->crs_utils->getType();
				break;
			case "CATEGORIES":
				$val = implode(", ", $this->crs_utils->getTopics());
				break;
			case "TOPICS":
				$val = $this->crs_utils->getContents();
				if (!$a_markup) {
					$val = strip_tags($val);
				}
				break;
			case "OBJECTIVES":
				$val = $this->crs_utils->getGoals();
				if (!$a_markup) {
					$val = strip_tags($val);
				}
				break;
			case "ID":
				$val = $this->crs_utils->getCustomId();
				break;
			case "STARTDATE":
				$val = $this->crs_utils->getFormattedStartDate();
				break;
			case "STARTTIME":
				$val = $this->crs_utils->getFormattedStartTime();
				break;
			case "ENDDATE":
				$val = $this->crs_utils->getFormattedEndDate();
				break;
			case "ENDTIME":
				$val = $this->crs_utils->getFormattedEndTime();
				break;
			case "SCHEDULE":
				$val = $this->crs_utils->getFormattedSchedule();
				break;
			case "OFFICER-NAME":
				$val = $this->crs_utils->getTrainingOfficerName();
				break;
			case "OFFICER-PHONE":
				$val = $this->crs_utils->getTrainingOfficerPhone();
				break;
			case "OFFICER-EMAIL":
				$val = $this->crs_utils->getTrainingOfficerEmail();
				break;
			case "ADMIN-FIRSTNAME":
				$val = $this->crs_utils->getMainAdminFirstname();
				break;
			case "ADMIN-FIRSTNAME":
				$val = $this->crs_utils->getMainAdminLastname();
				break;
			case "ADMIN-LASTNAME":
				$val = $this->crs_utils->getMainAdminPhone();
				break;
			case "ADMIN-EMAIL":
				$val = $this->crs_utils->getMainAdminEmail();
				break;
			case "TRAINER-NAME":
				$val = $this->crs_utils->getMainTrainerName();
				break;
			case "TRAINER-PHONE":
				$val = $this->crs_utils->getMainTrainerPhone();
				break;
			case "TRAINER-EMAIL":
				$val = $this->crs_utils->getMainTrainerEmail();
				break;
			case "ALL TRAINERS":
				$trainers = $this->crs_utils->getTrainers();
				$val = array();
				foreach ($trainers as $trainer) {
					$utils = gevUserUtils::getInstance($trainer);
					$val[] = $utils->getFormattedContactInfo();
				}
				$val = implode("<br />", $val);
				break;
			case "VENUE-NAME":
				$val = $this->crs_utils->getVenueTitle();
				break;
			case "VENUE-STREET":
				$val = $this->crs_utils->getVenueStreet();
				break;
			case "VENUE-HOUSENUMBER":
				$val = $this->crs_utils->getVenueHouseNumber();
				break;
			case "VENUE-ZIPCODE":
				$val = $this->crs_utils->getVenueZipcode();
				break;
			case "VENUE-CITY":
				$val = $this->crs_utils->getVenueCity();
				break;
			case "VENUE-PHONE":
				$val = $this->crs_utils->getVenuePhone();
				break;
			case "VENUE-INTERNET":
				$val = $this->crs_utils->getVenueHomepage();
				break;
			case "WEBINAR-LINK":
				$val = $this->crs_utils->getWebinarLink();
				break;
			case "WEBINAR-PASSWORD":
				$val = $this->crs_utils->getWebinarPassword();
				break;
			case "ACCOMODATION-NAME":
				$val = $this->crs_utils->getAccomodationTitle();
				break;
			case "ACCOMODATION-STREET":
				$val = $this->crs_utils->getAccomodationStreet();
				break;
			case "ACCOMODATION-HOUSENUMBER":
				$val = $this->crs_utils->getAccomodationHouseNumber();
				break;
			case "ACCOMODATION-ZIPCODE":
				$val = $this->crs_utils->getAccomodationZipcode();
				break;
			case "ACCOMODATION-CITY":
				$val = $this->crs_utils->getAccomodationCity();
				break;
			case "ACCOMODATION-PHONE":
				$val = $this->crs_utils->getAccomodationPhone();
				break;
			case "ACCOMODATION-EMAIL":
				$val = $this->crs_utils->getAccomodationEmail();
				break;
			case "BOOKED BY FIRSTNAME":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getFirstnameOfUserWhoBookedAtCourse($this->crs_utils->getId());
				}
				break;
			case "BOOKED BY LASTNAME":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getLastnameOfUserWhoBookedAtCourse($this->crs_utils->getId());
				}
				break;
			case "OPERATIONDAYS":
				$start = $this->crs_utils->getStartDate();
				$end = $this->crs_utils->getEndDate();
				
				if ($start && $end) {
					require_once("Services/TEP/classes/class.ilTEPCourseEntries.php");
					$tmp = ilTEPCourseEntries::getInstance($this->crs_utils->getCourse())
								->getOperationsDaysInstance();
					$op_days = $tmp->getDaysForUser($this->rec_user_id);
					foreach ($op_days as $key => $value) {
						$op_days[$key] = ilDatePresentation::formatDate($value);
					}
					$val = implode("<br />", $op_days);
				}
				else {
					$val = "Nicht verfügbar.";
				}
				break;
			case "OVERNIGHTS":
				if ($this->usr_utils !== null) {
					$tmp = $this->usr_utils->getOvernightDetailsForCourse($this->crs_utils->getCourse());
					$dates = array();
					foreach ($tmp as $date) {
						$d = ilDatePresentation::formatDate($date);
						$date->increment(ilDateTime::DAY, 1);
						$d .= " - ".ilDatePresentation::formatDate($date); 
						$dates[] = $d;
					}
					$val = implode("<br />", $dates);
				}
				break;
			case "PREARRIVAL":
				if ($this->usr_utils !== null) {
					$tmp = $this->usr_utils->getOvernightDetailsForCourse($this->crs_utils->getCourse());
					if (   count($tmp) > 0 
						&& $tmp[0]->get(IL_CAL_DATE) < $this->crs_utils->getStartDate()->get(IL_CAL_DATE)) {
						$val = $lng->txt("yes");
					}
					else {
						$val = $lng->txt("no");
					}
				}
				break;
			case "POSTDEPARTURE":
				if ($this->usr_utils !== null) {
					$tmp = $this->usr_utils->getOvernightDetailsForCourse($this->crs_utils->getCourse());
					if (   count($tmp) > 0 
						&& $tmp[count($tmp)-1]->get(IL_CAL_DATE) == $this->crs_utils->getEndDate()->get(IL_CAL_DATE)) {
						$val = $lng->txt("yes");
					}
					else {
						$val = $lng->txt("no");
					}
				}
				break;
			case "ORGANIZTIONAL":
				$val = $this->crs_utils->getOrgaInfo();
				break;
			case "LIST":
				$l = $this->crs_utils->getParticipants();
				$names = array();
				foreach ($l as $user_id) {
					$names[] = ilObjUser::_lookupFullname($user_id);
				}
				$val = implode("<br />", $names);
				break;
			default:
				return $a_placeholder_code;
		}
		
		$val = $this->maybeFormatEmptyField($val);
		if (!$a_markup) 
			$val = str_replace("<br />", "\n", $val);
		
		return $val;
	}

	// Phase 2: Attachments via Maildata
	function hasAttachments() {
		return false;
	}
	function getAttachments($a_lng) {
		return array();
	}
	
	function getRecipientUserId() {
		return $this->rec_user_id;
	}
	
	function initCourseData(gevCourseUtils $a_crs) {
		$this->cache = array();
		$this->crs_utils = $a_crs;
	}
	function setRecipient($a_user_id, $a_email, $a_name) {
		$this->cache = array();
		$this->rec_user_id = $a_user_id;
		$this->rec_email = $a_email;
		$this->rec_fullname =$a_fullname;
	}
	function initUserData(gevUserUtils $a_usr) {
		$this->cache = array();
		$this->usr_utils = $a_usr;
	}
}

?>