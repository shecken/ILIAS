<?php
require_once("Services/Cron/classes/class.ilCronHookPlugin.php");

class ilTroubleShootingPlugin extends ilCronHookPlugin {
	public function getPluginName() {
		return "TroubleShooting";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.ilTroubleShootingJob.php";
		$job = new ilTroubleShootingJob();
		return array($job);
	}

	function getCronJobInstance($a_job_id) {
		require_once $this->getDirectory()."/classes/class.ilTroubleShootingJob.php";
		return new ilTroubleShootingJob();
	}

	public function getDeleteUserActions()
	{
		global $ilDB;
		require_once __DIR__."/DeleteUserFromCourse/ilDeleteUserActions.php";
		require_once __DIR__."/DeleteUserFromCourse/ilDeleteuserDB.php";
		return new ilDeleteUserActions(new ilDeleteuserDB($ilDB));
	}

	public function getAddListActions()
	{
		global $ilDB;
		require_once __DIR__."/ParticipantList/AddList/ilAddListActions.php";
		require_once __DIR__."/ParticipantList/AddList/ilAddListDB.php";
		return new ilAddListActions(new ilAddListDB($ilDB));
	}

	public function getChangeListActions()
	{
		global $ilDB;
		require_once __DIR__."/ParticipantList/ChangeList/ilChangeListActions.php";
		require_once __DIR__."/ParticipantList/ChangeList/ilChangeListDB.php";
		return new ilChangeListActions(new ilChangeListDB($ilDB));
	}

	public function getTxtClosure()
	{
		return function($code) {
			return $this->txt($code);
		};
	}
}