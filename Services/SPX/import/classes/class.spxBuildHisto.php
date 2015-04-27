<?php

/**
 * Reconstructs EduBiographies for old courses.
 */
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/Tracking/classes/class.ilLPStatus.php");

class spxBuildHisto {
	static public function run() {
		ini_set("memory_limit","2048M"); 
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		global $ilDB;
		global $ilAppEventHandler;
		
		$course_res = $ilDB->query("SELECT od.obj_id "
								  ."  FROM object_data od"
								  ."  JOIN object_reference oref"
								  ."    ON od.obj_id = oref.obj_id"
								  ." WHERE od.type = 'crs'"
								  ."   AND oref.deleted IS NULL"
								  );
		
		while ($crs_rec = $ilDB->fetchAssoc($course_res)) {
			$crs_id = $crs_rec["obj_id"];
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$crs_members_object = $crs_utils->getCourse()->getMembersObject();
			echo "Building historizing entries for course $crs_id...\n";
			/*
			$admins = $crs_utils->getAdmins();
			foreach ($admins as $admin) {
				$crs_members_object->delete($admin);
				$crs_members_object->add($admin, IL_CRS_ADMIN);
			}
			
			$trainers = $crs_utils->getTrainers();
			foreach ($trainers as $trainer) {
				$crs_members_object->delete($trainer);
				$crs_members_object->add($trainer, IL_CRS_TUTOR);
			}*/
			
			$participants = $crs_utils->getParticipants();
			foreach ($participants as $participant) {
				if ($crs_utils->getBookingStatusOf($participant) === null) {
					$crs_members_object->delete($participant);
					$crs_utils->bookUser($participant);
				}

				// Fake Tracking event to create participation status
				$params = array
					( "obj_id" => $crs_id
					, "usr_id" => $participant
					, "status" => ilLPStatus::_lookupStatus($crs_id, $participant)
					, "evil_hack" => true
					);
				
				// Fake Tracking event to create participation status
				$params = array
					( "obj_id" => $crs_id
					, "usr_id" => $participant
					, "status" => ilLPStatus::_lookupStatus($crs_id, $participant)
					, "evil_hack" => true
					);
				
				$ilAppEventHandler->raise("Services/Tracking", "updateStatus", $params);
			}
		}
	}
}

?>