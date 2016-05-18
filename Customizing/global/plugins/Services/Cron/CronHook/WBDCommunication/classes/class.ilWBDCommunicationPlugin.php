<?php
require_once("Services/Cron/classes/class.ilCronHookPlugin.php");

/**
 * Plugin for Communication to WBD
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilWBDCommunicationPlugin extends ilCronHookPlugin {

	/**
	 * @inheritdoc
	 */
	public function getPluginName() {
		return "WBDCommunication";
	}

	/**
	 * @inheritdoc
	 */
	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.WBDCommunicationJob.php";
		$job = new WBDCommunicationJob($this);
		return array($job);
	}

	/**
	 * @inheritdoc
	 */
	function getCronJobInstance($a_job_id) {
		require_once $this->getDirectory()."/classes/class.WBDCommunicationJob.php";
		return new WBDCommunicationJob($this);
	}
}