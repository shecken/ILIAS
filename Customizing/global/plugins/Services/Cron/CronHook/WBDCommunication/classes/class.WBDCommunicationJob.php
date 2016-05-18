<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class		WBDCommunicationJob
 *
 * CronJob:	perform daily communication to WBD
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training-de>
 * @version $Id$
 */

require_once "Services/Cron/classes/class.ilCronManager.php";
require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";
require_once("Customizing/global/plugins/Services/Cron/CronHook/WBDCommunication/classes/class.ilWBDCommunicationConfig.php");

class WBDCommunicationJob extends ilCronJob {
	private $gDB;
	private $gLog;
	private $gLng;
	private $gRbacadmin;

	public function __construct($plugin) {
		global $ilDB, $ilLog, $lng, $rbacadmin;
		$this->gDB = $ilDB;
		$this->gLog = $ilLog;
		$this->gLng = $lng;
		$this->gRbacadmin = $rbacadmin;
		$this->plugin = $plugin;
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return "wbd_communication";
	}
	
	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->plugin->txt("job_title");
	}

	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation() {
		return true;
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
		$cron_result = new ilCronJobResult();
		$this->gLog->write("### WBDCommunicationJob: STARTING ###");

		$config_data = new ilWBDCommunicationConfig();
		$config_data->load();

		ilCronManager::ping($this->getId());

		$config = array();
		$config["siggy"] = array("sign_xml_url" => $config_data->siggy()."/sign_xml"
								,"fetch_token_url" => $config_data->siggy()."/fetch_token");
		$config["wbd"] = array("url" => $config_data->wbd());
		$config["client"] = array("path" => ILIAS_ABSOLUTE_PATH."/Services/GEV/WBD/classes/class.gevWBDDataCollector.php"
								 ,"chdir_folger" => ILIAS_ABSOLUTE_PATH);
		$config["working_services"] = array("services" => $config_data->actions());

		if($config_data->stornoRows() !== null) {
			$config["storno"] = array("rows" => $config_data->stornoRows());
		}

		if(in_array("cpRequest", $config_data->actions()) && $config_data->requestIds() !== null){
			$config["abfrage"] = array("usr_ids" => $config_data->requestIds());
		}

		ilCronManager::ping($this->getId());

		$this->writeIniFile($config, $config_data->configPath());

		ilCronManager::ping($this->getId());

		//shell_exec($config_data->runScript()." ".$config_data->configPath());

		ilCronManager::ping($this->getId());

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}

	/**
	 * create inifile
	 */
	protected function writeIniFile($assoc_arr, $path) {
		$content = "";
		foreach ($assoc_arr as $key => $elem) {
			$content .= "[".$key."]\n";

			foreach ($elem as $key2 => $elem2) {
				if(is_array($elem2)) {
					for($i = 0; $i < count($elem2); $i++) {
						$content .= $key2."[] = ".$elem2[$i]."\n";
					}
				} else {
					$content .= $key2." = ".$elem2."\n";
				}
			}

			$content .= "\n";
		}

		$content = substr($content,0,strlen($content) -1);

		if (!$handle = fopen($path, 'w+')) { 
			throw new Exception("unable to open file: ".$path);
		}

		$success = fwrite($handle, $content);
		fclose($handle);
	}
}