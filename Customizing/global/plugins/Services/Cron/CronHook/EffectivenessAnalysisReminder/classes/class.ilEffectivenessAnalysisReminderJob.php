<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

/**
 * Cronjob class for effectiveness analysis plugin
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilEffectivenessAnalysisReminderJob extends ilCronJob {
	public function __construct() {
		$this->plugin =
			ilPlugin::getPluginObject(IL_COMP_SERVICE, "Cron", "crnhk",
				ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Cron", "crnhk", $this->getId()));
	}

	public function getId() {
		return "effanalysisreminder";
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->plugin->txt("title");
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return $this->plugin->txt("description");
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
		$actions = $this->plugin->getActions();
		$cron_result = new ilCronJobResult();

		$all_superiors = $actions->getAllSuperiors();

		foreach($all_superiors as $superior_id) {
			foreach($actions->getUserIdsForFirstMail($superior_id) as $crs_id => $user_ids) {
				$actions->sendFirst($crs_id, array($superior_id), $user_ids);
			}
			ilCronManager::ping($this->getId());

			foreach($actions->getUserIdsForReminder($superior_id) as $crs_id => $user_ids) {
				$actions->sendReminder($crs_id, array($superior_id), $user_ids);
			}
			ilCronManager::ping($this->getId());
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}