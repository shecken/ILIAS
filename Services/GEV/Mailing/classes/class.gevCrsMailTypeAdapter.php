<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailTypeAdapter.php';

/**
 * GEV mail placeholders for courses
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 */
class gevCrsMailTypeAdapter extends ilMailTypeAdapter {
	private $placeholders = null;
	
	public function getCategoryNameLocalized($category_name, $lng) {
		return 'GEVCrs';
	}

	public function getTemplateTypeLocalized($category_name, $template_type, $lng) {
		return 'Generisch';
	}

	protected function getPlaceholders() {
		if ($this->placeholders == null) {
			$this->placeholders = array(
				  array("Mobil"						, "Cell phone number of the Participant")
				, array("Title"						, "Title of the Training")
				, array("Subtitle"					, "Subttitle of the Training")
				//, array("Lernart"					, "Lernart des Training")
				, array("Type"						, "Type of the Training")
				, array("Categories"				, "all Categories of the Training from Multiselect")
				, array("Topics"					, "Topics of the Training from Free Text")
				, array("Objectives"				, "Objectives and Use of the Training from Free Text")
				, array("ID"						, "Number of the Training")
				, array("Startdate"					, "Start date of the Training")
				, array("Starttime"					, "Time of the Beginning of the Training")
				, array("Enddate"					, "End date of the Training")
				, array("Endtime"					, "Time of the End of the Training")
				, array("Schedule"					, "Schedule of the Training")
				, array("Officer-Name"				, "Name of the Officer of the Training")
				, array("Officer-Phone"				, "Phonenumber of the Officer of the Training")
				, array("Officer-Email"				, "Email of the Officer of the Training")
				, array("Admin-Firstname"			, "Firstname of the Admin of the Training")
				, array("Admin-Lastname"			, "Lastname of the Admin of the Training")
				, array("Admin-Email"				, "Email of the Admin of the Training")
				, array("Trainer-Name"				, "Name of the Trainer of the Training")
				, array("Trainer-Phone"				, "Phonenumber of the Trainer of the Training")
				, array("Trainer-Email"				, "Email of the Trainer of the Training")
				, array("All Trainers"				, "All Trainers of the Training including Contact Information")
				, array("Venue-Name"				, "Name of the Venue of the Training")
				, array("Venue-Street"				, "Street of the Venue of the Training")
				, array("Venue-Housenumber"			, "House Number of the Venue of the Training")
				, array("Venue-Zipcode"				, "Zipcode of the Venue of the Training")
				, array("Venue-City"				, "City of the Venue of the Training")
				, array("Venue-Phone"				, "Phonenumber of the Venue of the Training")
				, array("Venue-Internet"			, "Homepage of the Venue of the Training")
				, array("Webinar-Link"				, "Link of the Webinar deposited at the Training")
				, array("Webinar-Password"			, "Password for the Webinar deposited at the Training")
				, array("Accomodation-Name"			, "Name of the Accomodation for the Training")
				, array("Accomodation-Street"		, "Street of the Accomodation for the Training")
				, array("Accomodation-Housenumber"	, "House Number of the Accomodation for the Training")
				, array("Accomodation-Zipcode"		, "Zipcode of the Accomodation for the Training")
				, array("Accomodation-City"			, "City of the Accomodation for the Training")
				, array("Accomodation-Phone"		, "Phone of the Accomodation for the Training")
				, array("Accomodation-Email"		, "Email of the Accomodation for the Training")
				, array("Booked by Firstname"		, "Firstname of the User who did the Booking on the Training")
				, array("Booked by Lastname"		, "Lastname of the User who did the Booking on the Training")
				, array("Operationdays"				, "Days of Operation of the Trainer on the Training")
				, array("Overnights"				, "Overnights of the User at the Training")
				, array("Prearrival"				, "'Yes' if user requested a prearrival, 'No' otherwise.")
				, array("Postdeparture"				, "'Yes' if user requested a postdeparture, 'No' otherwise.")
				, array("List"						, "List of all Participants of the Training")
				, array("Organizational"			, "Content of Field 'Organizational' in the Training Details")
				);
		}
	
		return $this->placeholders;
	}

	public function getPlaceholdersLocalized($category_name = '', $template_type = '', $lng = '') {
		$ret = array();

		foreach($this->getPlaceholders() as $item)
		{
			$ret[] = array(
				'placeholder_code'          => strtoupper($item[0]),
				'placeholder_name'          => $item[0],
				'placeholder_description'   => $item[1]
			);
		};

		return $ret;
	}

	public function getPlaceHolderPreviews($category_name = '', $template_type = '', $lng = '') {
		$ret = array();

		foreach($this->getPlaceholders() as $item)
		{
			$ret[] = array(
				'placeholder_code'			=> strtoupper($item[0]),
				'placeholder_content'       => $item[0]
			);
		}

		return $ret;
	}

	public function hasAttachmentsPreview() {
		return false;
	}

	public function getAttachmentsPreview() {

	}
}

?>