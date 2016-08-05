<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once(__DIR__."/ilActions.php");

class ilCleanExitUserJob extends ilCronJob {
	public function __construct() {
		global $ilLog, $lng;

		$this->gLog = $ilLog;
		$this->gLng = $lng;

		$this->actions = new ilActions();
	}

	public function getId() {
		return "exus";
	}

	public function getTitle() {
		return $this->gLng->txt("gev_exited_user_cleanup_title");
	}

	public function hasAutoActivation() {
		return true;
	}

	public function hasFlexibleSchedule() {
		return false;
	}

	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}

	public function getDefaultScheduleValue() {
		return 1;
	}

	public function run() {
		$cron_result = new ilCronJobResult();

		// I know, this is not timezone safe.
		$now = @date("Y-m-d");

		$exit_user = $this->actions->getExitedUserIds();

		foreach($exit_user as $user_id) {
			$usr = $this->actions->getUserObj($user_id);

			foreach ($this->actions->getBookedCoursesFor($user_id) as $crs_id) {
				$start_date = $this->actions->getStartDateOf($crs_id);
				if ($start_date === null) {
					$this->gLog->write("gevExitedUserCleanupJob: User $user_id was not removed from training $crs_id, since"
								 ." the start date of the training could not be determined.");
					continue;
				}

				if ($start_date->get(IL_CAL_DATE) >= $now) {
					$this->actions->cancelBookings($crs_id, $user_id);
					$this->actions->sendMail("participant_left_corporation", $crs_id, $user_id);
					
					$this->gLog->write("gevExitedUserCleanupJob: User $user_id was canceled from training $crs_id.");
				}
				else {
					$this->gLog->write("gevExitedUserCleanupJob: User $user_id was not removed from training $crs_id, since"
								 ." training start date expired: ".$start_date->get(IL_CAL_DATE)." < ".$now);
				}
			}

			$usr->setActive(false);
			$this->gLog->write("gevExitedUserCleanupJob: Deactivated user with id $user_id.");

			$this->actions->deassignOrgUnits($user_id);
			$this->gLog->write("gevExitedUserCleanupJob: Removed user with id $user_id from OrgUnit with id $orgu_id.");

			$this->actions->assignUserToExitOrgu($user_id);
			$this->gLog->write("gevExitedUserCleanupJob: Moved user with id $user_id to exit-OrgUnit.");

			try {
				$nas = $this->actions->getNAsOf($user_id);

				foreach ($nas as $na) {
					$this->actions->moveNAToNoAdviserOrgUnit($na);
					$this->gLog->write("gevExitedUserCleanupJob: Moved na $na of user $user_id to no-adviser-OrgUnit.");
				}
				if (count($nas) > 0) {
					$this->gLog->write("gevExitedUserCleanupJob: Removed NA-OrgUnit of $user_id.");
					$this->actions->removeNAOrgUnitOf($user_id);
				}
			}
			catch (Exception $e) {
				$this->gLog->write("gevExitedUserCleanupJob: ".$e);
			}

			try {
				$this->gLog->write("gevExitedUserCleanupJob: Set next wbd action to release for user: ".$user_id.".");
				$this->actions->setUserToWBDRelease($user_id);
			}catch (Exception $e) {
				$this->gLog->write("gevExitedUserCleanupJob: ".$e);
			}

			//update user and create a history entry
			$usr->read();
			$usr->setActive(false);
			$usr->update();
			
			// i'm alive!
			ilCronManager::ping($this->getId());
		}

		$this->gLog->write("gevExitedUserCleanupJob: purging empty na-org units.");
		$this->actions->purgeEmptyNABaseChildren();

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}