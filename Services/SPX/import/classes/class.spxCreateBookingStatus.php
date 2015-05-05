<?php

/**
 * Reconstructs EduBiographies for old courses.
 */
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/Tracking/classes/class.ilLPStatus.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php");

class spxCreateBookingStatus {
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
			
			echo "Working on course $crs_id\n";
			$ilLog->write("Working on course $crs_id");

			$participants = $crs_utils->getParticipants();
			foreach ($participants as $participant) {
				if ($crs_utils->getBookingStatusOf($participant) === null) {
					$ilDB->manipulate("INSERT INTO crs_book (crs_id, user_id, status, status_changed_by, status_changed_on) "
									 ."VALUES ( ".$ilDB->quote($crs_id, "integer")
									 ."       , ".$ilDB->quote($participant, "integer")
									 ."       , status = 1"
									 ."       , status_changed_by = 6"
									 ."       , status_changed_on = UNIX_TIMESTAMP()"
									 ."       )"
									 );
					echo "    Added booking record for $participant\n";
					$ilLog->write("    Added booking record for $participant");
							
					$params = array( "crs_obj_id" => $crs_id
								   , "user_id" => $participant
								   );

					$ilAppEventHandler->raise("Services/CourseBooking"," setStatus", $params);

					echo "    Faked booking event for $participant\n";
					$ilLog->write("    Faked booking event for $participant");
				}
				else {
					echo "   $participant already booked.\n";
					$ilLog->write("   $participant already booked.");
				}
			}
		}
		
		echo "</pre>";
	}
}

?>