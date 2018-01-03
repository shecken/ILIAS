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
		'SCHEDULE' => 'crsSchedule',
		'STARTDATE' => 'crsStartdate',
		'ENDDATE' => 'crsEnddate',
		'TRAINER_FIRST_NAME' => 'trainerFirstname',
		'TRAINER_LAST_NAME' => 'trainerLastname',
		'OFFICE_FIRST_NAME' => 'adminFirstname',
		'OFFICE_LAST_NAME' => 'adminLastname'
	);

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	/**
	 * @var ilObjCourse
	 */
	protected $crs_obj;

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


	/**
	 * @return string
	 */
	public function crsSchedule() {
		$schedule = array();
		$sessions = $this->getSessionAppointments();
		foreach ($sessions as $sortdat => $times) {
			list($date, $start, $end) = $times;
			$schedule[] = sprintf("%s, %s - %s", $date, $start, $end);
		}
		return implode('<br>', $schedule);
	}

	/**
	 * @return string
	 */
	public function crsStartdate() {
		$crs = $this->getCourseObject();
		return $crs->getCourseStart()->get(IL_CAL_FKT_DATE, "d.m.Y");

	}

	/**
	 * @return string
	 */
	public function crsEnddate() {
		$crs = $this->getCourseObject();
		return $crs->getCourseEnd()->get(IL_CAL_FKT_DATE, "d.m.Y");
	}

	/**
	 * @return string
	 */
	public function trainerFirstname() {
		$trainer = $this->getTrainer();
		if($trainer !== null) {
			return $trainer->getFirstname();
		}
		return $trainer;
	}

	/**
	 * @return string
	 */
	public function trainerLastname() {
		$trainer = $this->getTrainer();
		if($trainer !== null) {
			return $trainer->getLastname();
		}
		return $trainer;
	}

	/**
	 * @return string
	 */
	public function adminFirstname() {
		$admin = $this->getAdmin();
		if($admin !== null) {
			return $admin->getFirstname();
		}
		return $admin;
	}

	/**
	 * @return string
	 */
	public function adminLastname() {
		$admin = $this->getAdmin();
		if($admin !== null) {
			return $admin->getLastname();
		}
		return $admin;
	}

	/**
	 * Get session appointments from within the course
	 *
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return string
	 */
	protected function getSessionAppointments() {
		$vals = array();
		$sessions = $this->getAllChildrenOfByType($this->getCourseRefId(), "sess");

		if(count($sessions) > 0) {
			foreach ($sessions as $session) {
				$appointment = $session->getFirstAppointment();
				$sort_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "Ymd");
				$start_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
				$start_time = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i");
				$end_time = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i");
				$vals[$sort_date] = array($start_date, $start_time, $end_time);
			}
		}

		ksort($vals, SORT_NUMERIC);
		return $vals;
	}

	/**
	 * Get all children by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getAllChildrenOfByType($ref_id, $search_type) {
		global $DIC;
		$g_tree = $DIC->repositoryTree();
		$g_objDefinition = $DIC["objDefinition"];

		$childs = $g_tree->getChilds($ref_id);
		$ret = array();

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				$ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($g_objDefinition->isContainer($type)) {
				$rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
				if(! is_null($rec_ret)) {
					$ret = array_merge($ret, $rec_ret);
				}
			}
		}
		return $ret;
	}

	/**
	 * Get the course-object
	 *
	 * @return ilObjCourse
	 */
	protected function getCourseObject() {
		if(! $this->crs_obj) {
			$this->crs_obj = \ilObjectFactory::getInstanceByRefId($this->getCourseRefId());
		}
		return $this->crs_obj;
	}

	/**
	 * Get first member with trainer-role
	 *
	 * @return ilObjUser | null
	 */
	protected function getTrainer() {
		$participants = $this->getCourseobject()->getMembersObject();
		$trainers = $participants->getTutors();
		if(count($trainers) > 0) {
			$trainer_id = (int)$trainers[0];
			return new \ilObjUser($trainer_id);
		}
		return null;
	}

	/**
	 * Get first member with admin-role
	 *
	 * @return ilObjUser | null
	 */
	protected function getAdmin() {
		$participants = $this->getCourseobject()->getMembersObject();
		$admins = $participants->getAdmins();
		if(count($admins) > 0) {
			$admin_id = (int)$admins[0];
			return new \ilObjUser($admin_id);
		}
		return null;
	}

}
