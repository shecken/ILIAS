<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 *
 */
interface Recipient {

	/**
	 *
	 *
	 * @return string
	 */
	public function getMailAddress();

	/**
	 *
	 *
	 * @return string | null
	 */
	public function getUserId();

	/**
	 *
	 * @return string | null
	 */
	public function getUserName();



}

