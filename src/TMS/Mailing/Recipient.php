<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 *
 */
interface Recipient {

	/**
	 *
	 * @throws Exception if there is no mail address
	 * @return string
	 */
	public function getMailAddress();

	/**
	 *
	 * @return string | null
	 */
	public function getUserId();

	/**
	 *
	 * @return string | null
	 */
	public function getUserLogin();

	/**
	 *
	 * @return string | null
	 */
	public function getUserName();

	/**
	 * @param string 	$name
	 * @throws Exception if Recipient was constructed with an id
	 * @return Recipient
	 */
	public function withName($name);

	/**
	 * @param string 	$mail
	 * @throws Exception if Recipient was constructed with an id
	 * @return Recipient
	 */
	public function withMail($mail);




}

