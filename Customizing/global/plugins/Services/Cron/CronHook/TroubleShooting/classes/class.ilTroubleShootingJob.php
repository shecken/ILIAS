<?php
/* Copyright (c) 2018 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Trouble Shooting scripts to fix usage errors
 */
class ilTroubleShootingJob extends ilCronJob {

	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}
	/**
	 * @inheritdoc
	 */
	public function getId() {
		return "TroubleShooting";
	}
	
	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->plugin->txt("trouble_shooting_title");
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription() {
		return $this->plugin->txt("trouble_shooting_description");
	}

	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation() {
		return false;
	}
	
	/**
	 * @inheritdoc
	 */
	public function hasFlexibleSchedule() {
		return false;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleValue() {
		return 1;
	}

	/**
	 * @inheritdoc
	 */
	public function run() {
	}
}