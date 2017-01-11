<?php
include_once('./Services/EventHandling/interfaces/interface.ilAppEventListener.php');

class ilEffectivenessAnalysisAppEventListener implements ilAppEventListener {
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		if($a_event == "setStatusAndPoints") {
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			require_once("Services/Calendar/classes/class.ilDateTime.php");
			require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");

			$crs_id = $a_parameter["crs_obj_id"];
			$user_id = $a_parameter["user_id"];
			$crs_utils = gevCourseUtils::getInstance($crs_id);

			self::writeLog(__METHOD__, "Start Eff Anaylsis Handling");
			self::writeLog(__METHOD__, "Is online training:");
			self::writeDump($crs_utils->isOnlineTraining());

			if($crs_utils->isOnlineTraining()) {
				$due_date = date("Y-m-d", time() + 90 * 24 * 60 * 60);
			} else {
				$due_date = date("Y-m-d",$crs_utils->getEndDate()->increment(ilDateTime::DAY, 90));
			}

			self::writeLog(__METHOD__, "Due date: ".$due_date);
			self::writeDump($crs_utils->getParticipationStatusOf($user_id));
			if($crs_utils->getParticipationStatusOf($user_id) != ilParticipationStatus::STATUS_SUCCESSFUL) {
				self::writeLog(__METHOD__, "Deleting old entry");
				self::deleteEffAnalysis($user_id, $crs_id);
			} else {
				self::writeLog(__METHOD__, "Create new entry");
				self::insertEffAnalysis($user_id, $crs_id, $due_date);
			}

			self::writeLog(__METHOD__, "Finished");
		}

		if($a_event == "delete") {
			if($a_parameter["type"] == 'crs') {
				self::deleteEffAnalysisForCrsId($a_parameter["obj_id"]);
			}
		}
	}

	protected static function deleteEffAnalysis($user_id, $crs_id) {
		global $ilDB;

		$query = "DELETE FROM eff_analysis_due_date\n"
				." WHERE crs_id = ".$ilDB->quote($crs_id, "integer")."\n"
				."     AND user_id = ".$ilDB->quote($user_id, "integer");

		$ilDB->manipulate($query);
	}

	protected static function deleteEffAnalysisForCrsId($crs_id) {
		global $ilDB;

		$query = "DELETE FROM eff_analysis_due_date\n"
				." WHERE crs_id = ".$ilDB->quote($crs_id, "integer");

		$ilDB->manipulate($query);
	}

	protected static function insertEffAnalysis($user_id, $crs_id, $due_date) {
		global $ilDB;

		$query = "INSERT INTO eff_analysis_due_date\n"
				." (crs_id, user_id, due_date)\n"
				." VALUES\n"
				." (".$ilDB->quote($crs_id, "integer")."\n"
				."     , ".$ilDB->quote($user_id, "integer")."\n"
				."     , ".$ilDB->quote($due_date, "date")."\n"
				." )\n"
				." ON DUPLICATE KEY UPDATE due_date = ".$ilDB->quote($due_date, "date");

		$ilDB->manipulate($query);
	}

	protected static function writeLog($method, $message) {
		global $ilLog;
		$ilLog->write($method.": ".$message);
	}

	protected static function writeDump($message) {
		global $ilLog;
		$ilLog->dump($message);
	}
}