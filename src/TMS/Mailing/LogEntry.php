<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This is the object for a log entry
 */
class LogEntry {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var ilDateTime
	 */
	protected $date;

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	/**
	 * @var int
	 */
	protected $usr_id;

	/**
	 * @var string
	 */
	protected $usr_login;

	/**
	 * @var string
	 */
	protected $usr_name;

	/**
	 * @var string
	 */
	protected $usr_mail;

	/**
	 * @var string
	 */
	protected $mail_id;

	/**
	 * @var string
	 */
	protected $subject;
	/**
	 * @var string
	 */
	protected $msg;


	/**
	 * @param int 	$id
	 * @param ilDateTime  	$date
	 * @param int  	$crs_ref_id
	 * @param int  	$usr_id
	 * @param string  	$usr_login
	 * @param string  	$usr_name
	 * @param string  	$usr_mail
	 * @param string  	$mail_id
	 * @param string  	$subject
	 * @param string  	$msg
	 */
	public function __construct($id, \ilDateTime $date, $crs_ref_id,
			$usr_id, $usr_login, $usr_name, $usr_mail,
			$mail_id, $subject = '', $msg = '') {

		assert('is_int($id)');
		assert('is_int($crs_ref_id)');
		assert('is_int($usr_id)');
		assert('is_string($usr_login)');
		assert('is_string($usr_name)');
		assert('is_string($usr_mail)');
		assert('is_string($mail_id)');
		assert('is_string($subject)');
		assert('is_string($msg)');

		$this->id = $id;
		$this->date = $date;
		$this->crs_ref_id = $crs_ref_id;
		$this->usr_id = $usr_id;
		$this->usr_login = $usr_login;
		$this->usr_name = $usr_name;
		$this->usr_mail = $usr_mail;
		$this->mail_id = $mail_id;
		$this->subject = $subject;
		$this->msg = $msg;
	}

	/**
	* @return int
	*/
	public function getId() {
		return $this->id;
	}

	/**
	* @return \ilDateTime
	*/
	public function getDate() {
		return $this->date;
	}

	/**
	* @return string
	*/
	public function getDateAsString() {
		return $this->date->get(IL_CAL_DATETIME);
	}

	/**
	* @return int
	*/
	public function getCourseRefId() {
		return $this->crs_ref_id;
	}

	/**
	* @return int
	*/
	public function getUserId() {
		return $this->usr_id;
	}

	/**
	* @return string
	*/
	public function getUserLogin() {
		return $this->usr_login;
	}

	/**
	* @return string
	*/
	public function getUserName() {
		return $this->usr_name;
	}

	/**
	* @return string
	*/
	public function getUserMail() {
		return $this->usr_mail;
	}

	/**
	* @return string
	*/
	public function getMailId() {
		return $this->mail_id;
	}

	/**
	* @return string
	*/
	public function getSubject() {
		return $this->subject;
	}

	/**
	* @return string
	*/
	public function getMessage() {
		return $this->msg;
	}
}
