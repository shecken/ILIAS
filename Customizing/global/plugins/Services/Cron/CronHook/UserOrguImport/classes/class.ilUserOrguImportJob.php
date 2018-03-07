<?php
ini_set('memory_limit', '2048M');

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Cron/classes/class.ilCronJob.php";
require_once 'Services/Cron/classes/class.ilCronJobResult.php';
require_once 'Services/Cron/classes/class.ilCronManager.php';
require_once 'Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserOrguImport/classes/ErrorReporting/ilUOIErrorNotification.php';

use CaT\IliasUserOrguImport as DUOI;
use CaT\UserOrguImport as UOI;

/**
* Implementation of the cron job
*/
class ilUserOrguImportJob extends ilCronJob
{

	public function __construct($plugin)
	{
		global $DIC;
		$this->ec = new DUOI\ErrorReporting\ErrorCollection();
		$this->f = $plugin->getFactory($this->ec);
		$this->error_notification = new ilUOIErrorNotification($DIC['rbacreview']);
	}

	/**
	 * Get id
	 *
	 * @return string
	 */
	public function getId()
	{
		return 'user_orgu_import';
	}

	/**
	 * Is to be activated on "installation"
	 *
	 * @return boolean
	 */
	public function hasAutoActivation()
	{
		true;
	}

	/**
	 * Can the schedule be configured?
	 *
	 * @return boolean
	 */
	public function hasFlexibleSchedule()
	{
		false;
	}

	/**
	 * Get schedule type
	 *
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}

	/**
	 * Get schedule value
	 *
	 * @return int|array
	 */
	public function getDefaultScheduleValue()
	{
		1;
	}

	/**
	 * Get called if the cronjob is started
	 * Executing the ToDo's of the cronjob
	 */
	public function run()
	{
		$cron_result = new ilCronJobResult();
		try {
			ilCronManager::ping($this->getId());
			$this->updateUsers();
			ilCronManager::ping($this->getId());
			$this->updateOrgus();
			ilCronManager::ping($this->getId());
			$this->updateUserOrgus();
			ilCronManager::ping($this->getId());
			$this->exitUsers();
			ilCronManager::ping($this->getId());
			$this->cleanupEmptyOrgus();
			ilCronManager::ping($this->getId());
		} catch (\Exception $e) {
			$this->ec->addError('A fatal error occured, aborting import:'.$e->getMessage());
		}
		$this->error_notification->notifyAboutErrors($this->ec);
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);

		return $cron_result;
	}

	protected function updateUsers()
	{
		$u_f = $this->f->UserFactory();
		$source = $u_f->ExcelUsers();
		$excel_pnrs = $source->deliveredPNRs();
		$excel_users = $source->users();
		if ($excel_users) {
			$ilias_users = $u_f->UserLocator()->relevantUsersWithPNRs($excel_pnrs);
			$diff = $u_f->Difference($ilias_users, $excel_users);
			$this->f->UserFactory()->UserUpdater()->applyDiff($diff);
		} else {
			$this->ec->addError('No User data provided. User import skipped.');
		}
	}

	protected function updateOrgus()
	{
		$o_f = $this->f->OrguFactory();
		$excel_orgus = $o_f->ExcelOrgus()->orgus();
		if ($excel_orgus) {
			$ilias_orgus = $o_f->OrguLocator()->getRelevantOrgus();
			$diff = $o_f->Difference($ilias_orgus, $excel_orgus);
			$this->f->OrguFactory()->OrguUpdater()->applyDiff($diff);
		} else {
			$this->ec->addError('No Orgu data provided. Orgu import skipped.');
		}
	}

	protected function updateUserOrgus()
	{
		$uo_f = $this->f->UserOrguAssignmentsFactory();

		$excel_user_orgu = $uo_f->UserOrguExcel()->assignments();
		if ($excel_user_orgu) {
			$excel_pnrs = $this->f->UserFactory()->ExcelUsers()->deliveredPNRs();
			$ilias_user_orgu = $uo_f->UserOrguLocator()->getAssignmentsAmong($excel_pnrs);
			$diff = $uo_f->Difference($ilias_user_orgu, $excel_user_orgu);
			$uo_f->UserOrguUpdater()->applyDiff($diff);
		} else {
			$this->ec->addError('No Assignment data provided. Assignment import skipped.');
		}
	}

	protected function exitUsers()
	{
		$this->f->ExitUserManagement()->exitUsers();
	}

	protected function cleanupEmptyOrgus()
	{
		$this->f->OrgusCleanup()->cleanupEmptyOrgus();
	}
}
