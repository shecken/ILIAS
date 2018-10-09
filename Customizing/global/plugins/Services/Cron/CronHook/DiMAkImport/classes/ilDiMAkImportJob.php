<?php

namespace CaT\Plugins\DiMAkImport;

require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

/**
 * Import adp numbers from file to ilias db.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilDiMAkImportJob extends \ilCronJob
{
	public function __construct(
		\ilDiMAkImportPlugin $plugin,
		Import\ImportFiles $import,
		ErrorReporting\ErrorCollection $error_collection,
		ErrorReporting\ilUOIErrorNotification $error_notification
	) {
		$this->plugin = $plugin;
		$this->import = $import;
		$this->error_collection = $error_collection;
		$this->error_notification = $error_notification;
		$this->file_config = $plugin->getFileActions()->read();
	}
	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return "dimak_mediator_number_import";
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return $this->plugin->txt("dimak_mediator_number_import");
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
		return \ilCronJob::SCHEDULE_TYPE_DAILY;
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
		$cron_result = new \ilCronJobResult();

		$this->import->reset();
		\ilCronManager::ping($this->getId());

		$path = $this->file_config->getPath();
		if(is_null($path) || $path == "") {
			$cron_result->setStatus(\ilCronJobResult::STATUS_CRASHED);
			$cron_result->setMessage("No file source path configured.");
			return $cron_result;
		}

		$this->import->importFiles($path);
		\ilCronManager::ping($this->getId());

		$errors = $this->import->getErrors();
		if(count($errors) > 0) {
			$this->sendErrors($errors);
			$cron_result->setStatus(\ilCronJobResult::STATUS_OK);
			return $cron_result;
		}

		$file_path = $this->import->getDataFilePath();
		if($file_path == "") {
			$this->sendErrors(array("No data filepath"));
			$cron_result->setStatus(\ilCronJobResult::STATUS_OK);
			return $cron_result;
		}
		$this->saveAgentNumbers($file_path);

		$cron_result->setStatus(\ilCronJobResult::STATUS_OK);
		return $cron_result;
	}

	protected function sendErrors(array $errors)
	{
		$this->error_collection->reset();
		foreach ($errors as $error) {
			$this->error_collection->addError($error);
			\ilCronManager::ping($this->getId());
		}
		$this->error_notification->notifyAboutErrors($this->error_collection);
		\ilCronManager::ping($this->getId());
	}

	protected function saveAgentNumbers($file_path)
	{
		$actions = $this->plugin->getDataActions();
		$actions->truncate();
		$actions->save($file_path);
		return;
	}
}