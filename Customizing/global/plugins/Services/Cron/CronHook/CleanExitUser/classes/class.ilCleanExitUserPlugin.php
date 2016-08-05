<?php
/**
 * A cron-hook plugin to clean up exited users
 */

require_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
 
class ilCleanExitUserPlugin extends ilCronHookPlugin {
	function getPluginName() {
		return "CleanExitUser";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.ilCleanExitUserJob.php";
		$job = new ilCleanExitUserJob();
		return array($job);
	}

	function getCronJobInstance($a_job_id) {
		require_once $this->getDirectory()."/classes/class.ilCleanExitUserJob.php";
		return new ilCleanExitUserJob();
	}
}