<?php
require_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\DiMAkImport;
 
class ilDiMAkImportPlugin extends ilCronHookPlugin {
	public function getPluginName() {
		return "DiMAkImport";
	}

	public function getCronJobInstances() {
		$job = $this->getCronJobInstance();
		return array($job);
	}

	public function getCronJobInstance($a_job_id) {
		global $DIC;

		$fs = new DiMAkImport\Import\Filesystem();
		$error_collection = new DiMAkImport\ErrorReporting\ErrorCollection();
		$import = new DiMAkImport\Import\ImportFiles($fs);
		$error_notification = new DiMAkImport\ErrorReporting\ilUOIErrorNotification($DIC['rbacreview']);

		return new DiMAkImport\ilDiMAkImportJob($this, $import, $error_collection, $error_notification);
	}

	public function getFileActions()
	{
		if(is_null($this->file_actions)) {
			global $DIC;
			$this->file_actions = new DiMAkImport\Configuration\Files\FileActions(
				new DiMAkImport\Configuration\Files\ilFileStorage($DIC["ilSetting"])
			);
		}

		return $this->file_actions;
	}

	public function getDataActions()
	{
		if(is_null($this->data_actions)) {
			global $DIC;
			$this->data_actions = new DiMAkImport\Import\Data\Actions(
				new DiMAkImport\Import\Data\ilDB($DIC["ilDB"])
			);
		}

		return $this->data_actions;
	}
}