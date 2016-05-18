<?php
require_once("Services/Cron/classes/class.ilCronHookPlugin.php");

/**
 * Plugin for Communication ti WBD
 */
class ilWBDCommunicationPlugin extends ilCronHookPlugin {
	public function getPluginName() {
		return "WBDCommunication";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.WBDCommunicationJob.php";
		$job = new WBDCommunicationJob($this);
		return array($job);
	}

	function getCronJobInstance($a_job_id) {
		require_once $this->getDirectory()."/classes/class.WBDCommunicationJob.php";
		return new WBDCommunicationJob($this);
	}
}