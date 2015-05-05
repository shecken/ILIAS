<?php

/**
 * Reconstructs EduBiographies for old courses.
 */
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/Tracking/classes/class.ilLPStatus.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php");

class spxCheckBookingStatus {
	static public function run() {
		ini_set("memory_limit","2048M"); 
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		global $ilDB;
		global $ilLog;
		global $ilAppEventHandler;
		
		echo "<pre>";
		
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
			
			echo "Checking course $crs_id\n";

			$participants = $crs_utils->getParticipants();
			foreach ($participants as $participant) {
				if ($crs_utils->getBookingStatusOf($participant) === null) {
					echo "    $participant has no booking status.";
				}
			}
		}
		
		echo "</pre>";
	}
}

?>