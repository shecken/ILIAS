<?php
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

/**
 * Import adp numbers from file to ilias db.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class gevADPImportJob extends ilCronJob
{
	const ADP_FILE_PATH = "/var/drbd/www/files/UserOrguImport/adp.dat";
	const STELLE_FILE_PATH = "/var/drbd/www/files/UserOrguImport/stelle.dat";
	const DELIMETER = "%";

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return "gev_adp_import";
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return "Import von ADP Nummern";
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
		require_once("./Services/GEV/Import/classes/class.gevADPFile.php");
		require_once("./Services/GEV/Import/classes/class.gevADPDB.php");

		global $ilDB;

		$cron_result = new ilCronJobResult();
		$db = new gevADPDB($ilDB);
		$file = new gevADPFile();

		$adp_handle = $file->open(self::ADP_FILE_PATH);
		$stelle_handle = $file->open(self::STELLE_FILE_PATH);

		$results = array();

		$skip_first_loop = true;
		while ($adp = $file->readCSVLine($adp_handle, self::DELIMETER)) {
			if ($skip_first_loop || !is_numeric($adp[0])) {
				$skip_first_loop = false;
				continue;
			}
			$results[$adp[0]] = array();
		}

		$skip_first_loop = true;
		while ($stelle = $file->readCSVLine($stelle_handle, self::DELIMETER)) {
			if ($skip_first_loop || !is_numeric($stelle[5])) {
				$skip_first_loop = false;
				continue;
			}
			if (array_key_exists($stelle[5], $results)) {
				$results[$stelle[5]] = [
					'agent_status' => $stelle[6],
					'vms_text' => $stelle[7]
				];
			}
			ilCronManager::ping($this->getId());
		}

		$db->createEntries($results);

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}