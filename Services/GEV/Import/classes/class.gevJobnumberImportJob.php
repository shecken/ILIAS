<?php
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

/**
 * Import adp numbers from file to ilias db.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class gevJobnumberImportJob extends ilCronJob
{
	const STELLE_FILE_PATH = "/var/drbd/www/files/UserOrguImport/stelle.dat";
	const DELIMETER = "%";

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return "gev_jobnumber_import";
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
<<<<<<< HEAD:Services/GEV/Import/classes/class.gevJobnumberImportJob.php
		return "Import von Maklernummern";
=======
		return "Import von Makler Stellennummern";
>>>>>>> 48caa78a353273103b57a8e2a6d608b34c6793ab:Services/GEV/Import/classes/class.gevADPImportJob.php
	}

	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation()
	{
		return true;
	}
	
	/**
	 * @inheritdoc
	 */
	public function hasFlexibleSchedule()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleType()
	{
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleValue()
	{
		return 1;
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		require_once("./Services/GEV/Import/classes/class.gevJobnumberFile.php");
		require_once("./Services/GEV/Import/classes/class.gevJobnumberDB.php");

		global $ilDB;

		$cron_result = new ilCronJobResult();
		$db = new gevADPDB($ilDB);
		$file = new gevADPFile();

		$stelle_handle = $file->open(self::STELLE_FILE_PATH);

		$results = array();

		$skip_first_loop = true;
		while ($stelle = $file->readCSVLine($stelle_handle, self::DELIMETER)) {
			if ($skip_first_loop || !is_numeric($stelle[0])) {
				$skip_first_loop = false;
				continue;
			}

			$results[$stelle[0]] = [
				'agent_status' => $stelle[6],
				'vms_text' => $stelle[7]
			];

			ilCronManager::ping($this->getId());
		}

		$db->createEntries($results);

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}