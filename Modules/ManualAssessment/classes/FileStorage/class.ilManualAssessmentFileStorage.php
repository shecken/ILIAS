<?php
require_once("Modules/ManualAssessment/interfaces/FileStorage/interface.ManualAssessmentFileStorage.php");
include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
*
*/
class ilManualAssessmentFileStorage extends ilFileSystemStorage implements ManualAssessmentFileStorage
{
	public function __construct($a_container_id = 0)
	{
		parent::__construct(self::STORAGE_WEB, true, $a_container_id);
	}

	protected function getPathPostfix()
	{
		return 'mass_';
	}

	protected function getPathPrefix()
	{
		return 'MASS';
	}

	public function isEmpty()
	{
		$files = $this->readDir();

		return (count($files) == 0) ? true : false;
	}

	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	public function getAbsolutePath()
	{
		$path = parent::getAbsolutePath();
		$path .= "/user_".$this->user_id;

		return $path;
	}

	public function readDir()
	{
		if (!is_dir($this->getAbsolutePath())) {
			$this->create();
		}

		$fh = opendir($this->getAbsolutePath());
		$files = array();
		while ($file = readdir($fh)) {
			if ($file !="." && $file !=".." && !is_dir($this->getAbsolutePath()."/".$file)) {
				$files[] = $file;
			}
		}
		closedir($fh);

		return $files;
	}

	public function uploadFile($file)
	{
		$path = $this->getAbsolutePath();

		$clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file["name"]);
		$new_file = $path."/".$clean_name;

		if (move_uploaded_file($file["tmp_name"], $new_file)) {
			chmod($new_file, 0770);
			return true;
		}

		return false;
	}

	public function deleteCurrentFile()
	{
		$files = $this->readDir();
		$this->deleteFile($this->getAbsolutePath()."/".$files[0]);
	}

	public function getFilePath()
	{
		$files = $this->readDir();
		return $this->getAbsolutePath()."/".$files[0];
	}
}
