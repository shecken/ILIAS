<?php

/**
 * cat-tms-patch start
 */
require_once("Services/Component/classes/class.ilPluginAdmin.php");
require_once("Services/TMS/TrainingSearch/classes/class.ilTrainingSearchGUI.php");
require_once("Services/TMS/TrainingSearch/classes/TrainingSearchDB.php");

class ilTrainingSearchDB implements TrainingSearchDB {
	const UNLIMITED_MEMBER_SPOTS = "∞";

	public function __construct() {
		global $DIC;

		$this->g_db = $DIC->database();
		$this->g_tree = $DIC->repositoryTree();
	}

	/**
	 * @inheritdoc
	 */
	public function getBookableTrainingsFor($user_id) {
		$crs_infos = array();

		if(ilPluginAdmin::isPluginActive('xbkm') && ilPluginAdmin::isPluginActive('xccl')) {
			$crs_infos =  $this->getBookingModalitiesWithPermissionFor($user_id);
			$crs_infos = $this->addCourseIfUserIsNotBooked($crs_infos, $user_id);
			$crs_infos = $this->transformBkmToCourse($crs_infos);
			$crs_infos = $this->addCourseClassification($crs_infos);
		}

		return $crs_infos;
	}

	protected function addCourseClassification($crs_infos) {
		foreach ($crs_infos as $key => &$crs_info) {
			foreach($this->g_tree->getChildsByType($crs_info["crs"]->getRefId(), "xccl") as $cc_info) {
				$crs_info["xccl"] = ilObjectFactory::getInstanceByRefId($cc_info["ref_id"]);
			}
		}

		return $crs_infos;
	}

	/**
	 * @inheritdoc
	 */
	public function getBookableCourse($crs_title,
				$type,
				$start_date_str,
				$bookings_available,
				array $target_group,
				$goals,
				array $topics,
				$end_date_str,
				$city,
				$address,
				$costs = "KOSTEN"
	) {
		require_once("Services/TMS/TrainingSearch/classes/BookableCourse.php");
		return new BookableCourse(
				$crs_title,
				$type,
				$start_date_str,
				(string)$bookings_available,
				$target_group,
				$goals,
				$topics,
				$start_date_str." - ".$end_date_str,
				$city,
				$address,
				$costs
			);
	}

	/**
	 * Get all booking modalities user as permission to book with
	 *
	 * @param int 	$user_id
	 *
	 * @return array<int, ilObjBookingModalities>
	 */
	protected function getBookingModalitiesWithPermissionFor($user_id) {
		$op_id = ilRbacReview::_getOperationIdByName("book_by_this");
		$query = "SELECT xbkm_booking.obj_id, rbac_pa.ops_id, object_reference.ref_id FROM xbkm_booking".PHP_EOL
				." JOIN object_reference".PHP_EOL
				."     ON object_reference.obj_id = xbkm_booking.obj_id".PHP_EOL
				." JOIN rbac_ua".PHP_EOL
				."     ON rbac_ua.usr_id = ".$this->g_db->quote($user_id, "integer").PHP_EOL
				." JOIN rbac_pa".PHP_EOL
				."     ON rbac_pa.ref_id = object_reference.ref_id".PHP_EOL
				."         AND rbac_pa.rol_id = rbac_ua.rol_id".PHP_EOL
				." WHERE xbkm_booking.modus = ".$this->g_db->quote("self_booking", "text");

		$ret = array();
		$res = $this->g_db->query($query);
		while($row = $this->g_db->fetchAssoc($res)) {
			$ops = unserialize(stripslashes($row["ops_id"]));
			if(in_array($op_id, $ops)) {
				$bm = ilObjectFactory::getInstanceByRefId($row["ref_id"]);
				$ret[] = array("xbkm" => $bm);
			}
		}

		return $ret;
	}

	/**
	 * Adds course object if user is not booked or drops bkm
	 *
	 * @param array<int, ilObjBookingModalities>
	 * @param int 	$user_id
	 *
	 * @return array<int, ilObjCourse | ilObjBookingModalities>
	 */
	protected function addCourseIfUserIsNotBooked(array $bms, $user_id) {
		foreach ($bms as $key => &$value) {
			$bm = ilObjectFactory::getInstanceByRefId($value["xbkm"]->getRefId());

			if($parent_crs = $bm->getParentCourse()) {
				require_once("Modules/Course/classes/class.ilCourseParticipants.php");
				if(!ilCourseParticipants::_isParticipant($parent_crs->getRefId(), $user_id)) {
					$value["crs"] = $parent_crs;
					continue;
				}
			}

			unset($bms[$key]);
		}

		sort($bms);
		return $bms;
	}

	/**
	 * transform array to get bkm with same crs in a single array
	 *
	 * @param array<int, ilObjCourse | ilObjBookingModalities>
	 * 
	 * @return array<int, ilObjCourse | ilObjBookingModalities[]>
	 */
	protected function transformBkmToCourse($bms) {
		$ret = array();

		uasort($bms, function($a, $b) {
			return strcmp((string)$a["crs"]->getRefId(), (string)$b["crs"]->getRefId());
		});

		$crs_ref_id = null;
		foreach ($bms as $key => $value) {
			if($crs_ref_id != $value["crs"]->getRefId()) {
				$crs_ref_id = $value["crs"]->getRefId();
				$ret[$crs_ref_id]["crs"] = $value["crs"];
			}

			$ret[$crs_ref_id]["xbkm"][] = $value["xbkm"];
		}

		return $ret;
	}
}

/**
 * cat-tms-patch end
 */