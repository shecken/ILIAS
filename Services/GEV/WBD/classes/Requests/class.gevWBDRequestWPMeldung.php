<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service WPMeldung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPMeldung.php");
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");

class gevWBDRequestWPMeldung extends WBDRequestWPMeldung {
	use gevWBDRequest;

	protected $error_group;

	protected function __construct($data) {
		$this->error_group = gevWBDError::ERROR_GROUP_CRS;

		$this->defineValuesToTranslate();
		$dic_errors = $this->translate($data["user_id"], $data["row_id"], $data["crs_id"]);

		$this->type 				= new WBDData("LernArt",$this->translate_value["LernArt"]);
		$this->wbd_topic 			= new WBDData("LernInhalt",$this->translate_value["LernInhalt"]);

		$this->title 				= new WBDData("Weiterbildung",$data["title"]);
		$this->begin_date 			= new WBDData("SeminarDatumVon",$data["begin_date"]);
		$this->end_date 			= new WBDData("SeminarDatumBis",$data["end_date"]);
		$this->credit_points 		= new WBDData("WeiterbildungsPunkte",$data["credit_points"]);
		$this->internal_booking_id	= new WBDData("InterneBuchungsId",$data["row_id"]);
		$this->agent_id 			= new WBDData("VermittlerId",$data["bwv_id"]);

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
		$this->crs_id = $data["crs_id"];
		$this->begin_of_certification = $data["begin_of_certification"];

		$check_errors = $this->checkData();
		$errors = $check_errors + $dic_errors;

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestWPMeldung::__construct:checkData failed",0,null, $errors);
		}
	}

	public static function getInstance(array $data) {
		try {
			return new gevWBDRequestWPMeldung($data);
		}catch(myLogicException $e) {
			return $e->options();
		}
	}

	/**
	* checked all given data
	*
	* @throws LogicException
	* 
	* @return string
	*/
	protected function checkData() {
		return $this->checkSzenarios();
	}

	/**
	* creates the success object VvErstanlage
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessWPMeldung($response, $this->begin_of_certification, $this->user_id);
	}

	/**
	* gets the row id
	*
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets the user_id
	*
	* @return integer
	*/
	public function userId() {
		return $this->user_id;
	}

	/**
	* gets a new WBD Error
	*
	* @return integer
	*/
	public function createWBDError($message) {
		$reason = $this->parseReason($message);
		$this->wbd_error = self::createError($reason, $this->error_group, $this->user_id, $this->row_id, $this->crs_id);
	}

	protected function defineValuesToTranslate() {
		$this->translate_value = array("LernArt" => array("field" => "type", "group" => gevWBDDictionary::SEARCH_IN_COURSE_TYPE)
									 , "LernInhalt" => array("field" => "wbd_topic", "group" => gevWBDDictionary::SEARCH_IN_STUDY_CONTENT)
								);
	}
}