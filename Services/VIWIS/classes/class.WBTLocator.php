<?php
require_once 'Services/VIWIS/config/cfg.wbt_data.php';
require_once 'Services/VIWIS/exceptions/class.WBTLocatorException.php';
class WBTLocator {
	const WBT_TYPE_SINGLESCO = 'singlesco';
	const WBT_TYPE_MULTISCO = 'multisco';

	public function __construct(ilDB $db) {
		$this->db = $db;
	}

	public static $wbt_locations;

	public function getRedirectLinksById($wbt_id) {
		$data = self::$wbt_locations[$wbt_id];

		$ref_id = $data['ref_id'];
		if(!$ref_id) {
			throw new WBTLocatorException('no corresponding wbt found for '.$wbt_id);
		}

		$type = $daty['type'];
		switch($type) {
			case self::WBT_TYPE_SINGLESCO:
				$ids = $this->getJumpTosByRefIdSinglesco($this->getManifestSinglesco($ref_id));
			case self::WBT_TYPE_MULTISCO:
				$ids = $this->getJumpTosByRefIdMultisco($this->getManifestMultisco($ref_id));
			default:
				throw new WBTLocatorException('unknown type '.$type);
		}
		return $this->getLinksByIds($ids, $wbt_id);
	}

	/**
	 * Get an assiciative array of question ids (like 1.2.3.4.5)
	 * to the corresponding link to open the corresponding wbt at the
	 * right spot.
	 */
	protected function getLinksByIds(array $ids, $wbt_id) {

	}
}