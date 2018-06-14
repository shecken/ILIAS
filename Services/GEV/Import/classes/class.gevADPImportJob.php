<?php
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

/**
 * Import adp numbers from file to ilias db.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class gevADPImportJob extends ilCronJob
{
	const IV_FILE_PATH = "/var/drbd/www/files/UserOrguImport/adp.dat";
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
		$iv_file = new gevADPFile();
		
		$handle = $iv_file->open(self::IV_FILE_PATH);
		
		$results = array();
		while ($tmp = $iv_file->readCSVLine($handle, self::DELIMETER)) {
			$results[] = $tmp[0];
		}

		if ($results[0] == "DPNR") {
			array_shift($results);
		}
		$db->createEntries($results);

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}