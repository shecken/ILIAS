<?php

namespace CaT\Plugins\DiMAkImport\Import;
use CaT\Plugins\DiMAkImport\ErrorReporting\ErrorCollection;
use CaT\Plugins\DiMAkImport\Configuration\Files\FileConfig;

/**
 * This class provides the means to import files to ilias from some
 * deployment path and to look up the current soource-files.
 */

class ImportFiles
{
	const IMPORT_CHECK_FILE_REGEX = '#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}\_CHECK\.txt$#';
	const IMPORT_DATA_FILE_REGEX = '#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}\_VMS\.txt$#';

	const KEY_CHECK_FILE = "check";
	const KEY_DATA_FILE = "data";

	protected $filesystem;

	public function __construct(Filesystem $fs)
	{
		$this->filesystem = $fs;
		$this->file_data = array();
		$this->errors = array();
	}

	public function importFiles($deployment_path)
	{
		$fs = $this->filesystem;
		$storage_path = $fs->getAbsolutePath();

		$files = array();
		$file = array_shift(preg_grep(self::IMPORT_CHECK_FILE_REGEX, $fs->readDir($deployment_path)));
		if(!is_null($file)) {
			$files[self::KEY_CHECK_FILE] = $file;
		}

		$file = array_shift(preg_grep(self::IMPORT_DATA_FILE_REGEX, $fs->readDir($deployment_path)));
		if(!is_null($file)) {
			$files[self::KEY_DATA_FILE] = $file;
		}

		foreach ($files as $file) {
			$ret = $fs->move($deployment_path.DIRECTORY_SEPARATOR.$file, $storage_path.DIRECTORY_SEPARATOR.$file);
		}

		$import = true;
		if(count($files) != 2) {
			$this->errors[] = "Number of files is not equal to 2";
			$import = false;
		}

		if(!$this->checkFilename($files)) {
			$this->errors[] = "File names are not correctly";
			$import = false;
		}

		if(!$this->filesReadable($files, $storage_path)) {
			$this->errors[] = "Files are not readable";
			$import = false;
		}

		$check_file_content = $this->readFile($files[self::KEY_CHECK_FILE], $storage_path);
		if(!$this->checkFile($check_file_content, $files[self::KEY_DATA_FILE], $storage_path)) {
			$this->errors[] = "checksum check failed";
			$import = false;
		}

		if($import) {
			$this->file_path = $storage_path.DIRECTORY_SEPARATOR.$files[self::KEY_DATA_FILE];
		}
	}

	protected function checkFilename(array $files)
	{
		$date = date('Y-m-d', time() /* + (24 * 3600)*/); //tomorrow
		if(strpos($files[self::KEY_CHECK_FILE], $date."_CHECK.txt") === false
			|| strpos($files[self::KEY_DATA_FILE], $date."_VMS.txt" ) === false
		) {
			return false;
		}

		return true;
	}

	protected function filesReadable(array $files, $path)
	{
		if(!is_readable($path.DIRECTORY_SEPARATOR.$files[self::KEY_CHECK_FILE])
			|| !is_readable($path.DIRECTORY_SEPARATOR.$files[self::KEY_DATA_FILE])
		) {
			return false;
		}

		return true;
	}

	protected function checkFile(array $check_file_content, $file, $path) 
	{
		if($check_file_content[0] != $file
			|| md5_file($path.DIRECTORY_SEPARATOR.$file) != $check_file_content[1]
		) {
			return false;
		}

		return true;
	}

	protected function readFile($file, $path)
	{
		$f_res = fopen($path.DIRECTORY_SEPARATOR.$file, 'r');

		$content = array();
		while($ln = fgets($f_res)) {
			$content[] = trim($ln);
		}

		return $content;
	}

	public function reset()
	{
		$this->file_path = "";
		$this->errors = array();
	}

	public function getDataFilePath()
	{
		return $this->file_path;
	}

	public function getErrors()
	{
		return $this->errors;
	}
}
