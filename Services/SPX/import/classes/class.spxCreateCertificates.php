<?php

/**
 * Reconstructs EduBiographies for old courses.
 */
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/Tracking/classes/class.ilLPStatus.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php");
require_once("./Modules/Course/classes/class.ilCourseCertificateAdapter.php");
require_once("./Services/Certificate/classes/class.ilCertificate.php");

class spxCreateCertificates {
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
								  ."  JOIN il_certificate cert"
								  ."    ON cert.obj_id = od.obj_id"
								  ." WHERE od.type = 'crs'"
								  ."   AND oref.deleted IS NULL"
								  );
		
		while ($crs_rec = $ilDB->fetchAssoc($course_res)) {
			$crs_id = $crs_rec["obj_id"];
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$crs_members_object = $crs_utils->getCourse()->getMembersObject();
			
			echo "Working on course $crs_id\n";
			$ilLog->write("Working on course $crs_id");

			$cert_res = $ilDB->query("SELECT row_id, usr_id"
									."  FROM hist_usercoursestatus"
									." WHERE crs_id = ".$ilDB->quote($crs_id, "integer")
									."   AND hist_historic = 0"
									."   AND certificate IN (-1, 0)"
									."   AND participation_status = 'status_successful'"
									);
			
			while ($cert_rec = $ilDB->fetchAssoc($cert_res)) {
				echo "    No certificate for ".$cert_rec["usr_id"]."\n";
				$ilLog->write("    No certificate for ".$cert_rec["usr_id"]);

				$ex_res = $ilDB->query("SELECT row_id, certificate"
									  ."  FROM hist_usercoursestatus"
									  ." WHERE crs_id = ".$ilDB->quote($crs_id, "integer")
									  ."   AND usr_id = ".$ilDB->quote($cert_rec["usr_id"], "integer")
									  ."   AND certificate NOT IN (-1, 0)"
									  );
				if ($ex_rec = $ilDB->fetchAssoc($ex_res)) {
					echo "    Using existing certificate ".$ex_rec["certificate"]." from row ".$ex_rec["row_id"]."\n";
					$ilLog->write("    Using existing certificate ".$ex_rec["certificate"]." from row ".$ex_rec["row_id"]);
					$ilDB->manipulate("UPDATE hist_usercoursestatus"
									 ."   SET certificate = ".$ilDB->quote($ex_rec["certificate"], "integer")
									 ." WHERE row_id = ".$ilDB->quote($cert_rec["row_id"], "integer")
									 );
				}
				else {
					echo "    Try to create new certificate.\n";
					$ilLog->write("    Try to create new certificate.");
					$course_class = ilObjectFactory::getClassByType('crs');
					$course_obj = new $course_class($crs_id, false);
					$certificate_adapter = new ilCourseCertificateAdapter($course_obj);
					$certificate = new ilCertificate($certificate_adapter);
					$data = $certificate->outCertificate(array("user_id" => $cert_rec["usr_id"]), false);
					if (!$data) {
						echo "!   Could not create certificate. ilServer is busy?\n";
						$ilLog->write("!   Could not create certificate. ilServer is busy?");
						continue;
					}
					$certfile_id = $ilDB->nextId('hist_certfile');
					$ilDB->insert( 'hist_certfile', 
								   array( 'row_id' => array("integer", $certfile_id), 
										  'certfile' => array("text", base64_encode($data)) 
										)
								 );
					$ilDB->manipulate("UPDATE hist_usercoursestatus"
									 ."   SET certificate = ".$ilDB->quote($certfile_id, "integer")
									 ." WHERE row_id = ".$ilDB->quote($cert_rec["row_id"], "integer")
									 );
					echo "    Successfully created certificate.\n";
					$ilLog->write("    Successfully created certificate.");
					
				}
			}
		}
		echo "</pre>";
	}
}

?>