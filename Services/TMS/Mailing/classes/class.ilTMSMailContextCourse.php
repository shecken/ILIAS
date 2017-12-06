<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * Course-related placeholder-values
 */
class ilTMSMailContextCourse implements Mailing\MailContext {
	private static $PLACEHOLDER = array(
		'COURSE_TITLE' => 'crsTitle',
		'COURSE_LINK' => 'crsLink',
	);

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	/**
	 * @var ilObjCourse
	 */
	protected $crs;

	public function __construct($crs_ref_id) {
		assert('is_int($crs_ref_id)');
		$this->crs_ref_id = $crs_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		if(array_key_exists($placeholder_id, $this::$PLACEHOLDER)){
			$func = $this::$PLACEHOLDER[$placeholder_id];
			return $this->$func();
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array_keys($this::$PLACEHOLDER);
	}

	/**
	 * @return int
	 */
	public function getCourseRefId() {
		return $this->crs_ref_id;
	}

	/**
	 * @return string
	 */
	public function crsTitle() {
		global $ilObjDataCache;
		$obj_id = $ilObjDataCache->lookupObjId($this->getCourseRefId());
		return $ilObjDataCache->lookupTitle($obj_id);
	}

	/**
	 * @return string
	 */
	public function crsLink() {
		require_once './Services/Link/classes/class.ilLink.php';
		return ilLink::_getLink($this->getCourseRefId(), 'crs');
	}

}
