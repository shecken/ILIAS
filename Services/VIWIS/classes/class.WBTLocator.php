<?php
require_once 'Services/VIWIS/config/cfg.wbt_data.php';
require_once 'Services/VIWIS/exceptions/class.WBTLocatorException.php';
class WBTLocator {
	const WBT_TYPE_SINGLESCO = 'singlesco';
	const WBT_TYPE_MULTISCO = 'multisco';

	public static $wbt_locations;

	public function getJumpTosById($wbt_id) {
		$ref_id = $wbt_locations[$wbt_id];
		if(!$ref_id) {
			throw new WBTLocatorException('no corresponding wbt found for '.$wbt_id);
		}
	}
}