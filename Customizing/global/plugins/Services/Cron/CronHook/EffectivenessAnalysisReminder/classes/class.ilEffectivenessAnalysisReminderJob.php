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
	/**
	 * @var ilObjUser
	 */
	protected $gUser;

	/**
	 * @var ilLog
	 */
	protected $gLog;

	/**
	 * @var EffectivenessAnalysisFirst
	 */
	protected $first_reminder;

	/**
	 * @var EffectivenessAnalysisSecond
	 */
	protected $second_reminder;

	public function __construct() {
		global $ilUser, $ilLog;;
		$this->gUser = $ilUser;
		$this->gLog = $ilLog;

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
			foreach($actions->getOpenEffectivenessAnalysis($superior_id) as $crs_id => $superiors) {
				$send_first = false;

				if($actions->shouldSendFirstReminder($crs_id)) {
					$this->gLog->write("SEND FIRST REMINDER");
					$actions->sendFirst($crs_id, $superiors);
					$send_first = true;
				}
				ilCronManager::ping($this->getId());

				if(!$send_first && $actions->shouldSendSecondReminder( $crs_id)) {
					$this->gLog->write("SEND SECOND REMINDER");
					$actions->sendSecond($crs_id, $superiors);
				}
				ilCronManager::ping($this->getId());
			}
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}