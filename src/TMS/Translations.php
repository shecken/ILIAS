<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS;


/**
 * This is the interface to a decorator for accumulating translations.
 */
interface Translations  {

	/**
	 * @return array <string, string>
	 */
	public function getTxts();

	/**
	 * @param 	string 	$id
	 * @return 	string
	 */
	public function getTxt($id);
}
