<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Cron/classes/class.ilCronHookPlugin.php");

/**
 * Plugin for effectiveness alalysis
 *
 * @author Stefan Hecken <stefan.hecken@cencepts-and-training.de>
 */
class ilEffectivenessAnalysisReminderPlugin extends ilCronHookPlugin {
	public function getPluginName() {
		return "EffectivenessAnalysisReminder";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.ilEffectivenessAnalysisReminderJob.php";
		$job = new ilEffectivenessAnalysisReminderJob();
		return array($job);
	}

	function getCronJobInstance($a_job_id) {
		require_once $this->getDirectory()."/classes/class.ilEffectivenessAnalysisReminderJob.php";
		return new ilEffectivenessAnalysisReminderJob();
	}

	/**
	 * Get a closure to get txts from plugin.
	 *
	 * @return \Closure
	 */
	public function txtClosure() {
		return function($code) {
			return $this->txt($code);
		};
	}

	/**
	 * Get the ilActions
	 *
	 * @return ilEffAnalysisActions
	 */
	public function getActions() {
		if($this->actions === null) {
			global $ilDB, $ilLog;
			require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
			$eff_analsis = new gevEffectivenessAnalysis();

			require_once $this->getDirectory()."/classes/ilEffectivenessAnalysisReminderDB.php";
			$db = new ilEffectivenessAnalysisReminderDB($ilDB);

			require_once $this->getDirectory()."/classes/ilEffAnalysisActions.php";
			$this->actions = new ilEffAnalysisActions($db, $ilLog, $eff_analsis);
		}

		return $this->actions;
	}
}