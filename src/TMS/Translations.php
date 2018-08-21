<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS;


/**
 * A translation will resolve an id/label to a text.
 */
interface Translations  {

	/**
	 * Return the resolved text for $id.
	 * @param 	string 	$id
	 * @return 	string
	 */
	public function getTxt($id);
}
