<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service WPAbfrage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPAbfrage.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");

class gevWBDRequestWPAbfrage extends WBDRequestWPAbfrage {
	use gevWBDRequest;

	protected $error_group;

	protected function __construct($data) {
		$this->error_group = gevWBDError::ERROR_GROUP_USER;

		$this->defineValuesToTranslate();
		$dic_errors = $this->translate($data, $data["user_id"], $data["row_id"]);

		$this->certification_period 	= new WBDData("ZertifizierungsPeriode",$this->translate_value["ZertifizierungsPeriode"]);

		$this->agent_id 				= new WBDData("VermittlerId",$data["bwv_id"]);

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];

		$check_errors = $this->checkData();
		$errors = $check_errors + $dic_errors;

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestWPAbfrage::__construct:checkData failed",0,null, $errors);
		}
	}

	public static function getInstance(array $data) {
		try {
			return new gevWBDRequestWPAbfrage($data);
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
		$this->wbd_success = new gevWBDSuccessWPAbfrage($response,$this->user_id);
	}

	/**
	* gets a new WBD Error
	*
	* @return integer
	*/
	public function createWBDError($message) {
		$reason = $this->parseReason($message);
		$this->wbd_error = self::createError($reason, $this->error_group, $this->user_id, $this->row_id);
	}

	protected function defineValuesToTranslate() {
		$this->translate_value = array("ZertifizierungsPeriode" => array("field" => "certification_period", "group" => gevWBDDictionary::SEARCH_IN_CERTIFICATION_PERIOD));
	}
}