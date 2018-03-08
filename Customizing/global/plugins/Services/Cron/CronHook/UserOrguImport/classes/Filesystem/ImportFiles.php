<?php

namespace CaT\IliasUserOrguImport\Filesystem;

use CaT\IliasUserOrguImport\ErrorReporting\ErrorCollection as ErrorCollection;

/**
 * This class provides the means to import files to ilias from some
 * deployment path and to look up the current soource-files.
 */

class ImportFiles
{

	const IMPORT_FILE_REGEX_CHECK = '#^Check\\_[0-9]{4}\\-[0-9]{2}\\-[0-9]{2}\\.csv$#';
	const IMPORT_FILE_REGEX_DATA = '#^SAP\\_[0-9]{4}\\-[0-9]{2}\\-[0-9]{2}\\.xlsx$#';

	protected $user;
	protected $orgu;
	protected $user_orgu;

	protected $error_collection;
	protected $filesystem;

	public function __construct(Filesystem $fs, ErrorCollection $error_collection, FilesystemConfig $file_config)
	{
		$this->error_collection = $error_collection;
		$this->filesystem = $fs;
		$deployment_path = $file_config->deploymentPath();
		if ($deployment_path) {
			$this->importFiles($deployment_path, $this->filesystem);
		}
		$this->loadAndCheckCurrentFiles($this->filesystem);
	}

	protected function importFiles($deployment_path, $fs)
	{
		$storage_path = $fs->getAbsolutePath();
		$files = preg_grep(self::IMPORT_FILE_REGEX_CHECK, $fs->readDir($deployment_path));
		$files = array_merge($files, preg_grep(self::IMPORT_FILE_REGEX_DATA, $fs->readDir($deployment_path)));

		foreach ($files as $file) {
			$fs->move($deployment_path.DIRECTORY_SEPARATOR.$file, $storage_path.DIRECTORY_SEPARATOR.$file);
		}
	}

	protected function loadAndCheckCurrentFiles($fs)
	{
		$date = date('Y-m-d', time() + (24 * 3600)); //tomorrow
		$storage_path = $fs->getAbsolutePath();
		$files = preg_grep(self::IMPORT_FILE_REGEX_CHECK, $fs->readDir($storage_path));
		sort($files);
		$file = array_pop($files);

		while ($file > 'Check_'.$date) {
			$file = array_pop($files);
		}

		if ($file !== null) {
			$this->readCheckFileAndAssignSources($file, $fs);
		} else {
			$this->error_collection->addError('No sources found prior to '.$date);
		}
	}

	protected function readCheckfileAndAssignSources($current_file, $fs)
	{
		$storage_path = $fs->getAbsolutePath();
		if ($f_res = fopen($storage_path.DIRECTORY_SEPARATOR.$current_file, 'r')) {
			$row = fgetcsv($f_res, 0, ';');
			$file = $row[0];
			$checksum = $row[1];
			if (!is_readable($storage_path.DIRECTORY_SEPARATOR.$current_check_file)) {
				$this->error_collection->addError($current_check_file.' is not readable');
				continue;
			}
			if ($this->check($checksum, $storage_path.DIRECTORY_SEPARATOR.$file)) {
				$this->assignFile($storage_path, $file);
			} else {
				$this->error_collection->addError('checksum check failed '.$current_check_file);
			}
		} else {
			$this->error_collection->addError('checksum file '.$current_check_file.' not readable');
		}
	}

	protected function check($checksum, $file_path)
	{
		return md5_file($file_path) === $checksum;
	}

	protected function assignFile($storage_path, $filename)
	{
		$this->user = $storage_path.DIRECTORY_SEPARATOR.$filename;
		$this->orgu = $storage_path.DIRECTORY_SEPARATOR.$filename;
		$this->user_orgu = $storage_path.DIRECTORY_SEPARATOR.$filename;
	}

	public function getCurrentUserFilePath()
	{
		return $this->user;
	}

	public function getCurrentOrguFilePath()
	{
		return $this->orgu;
	}

	public function getCurrentUserOrguFilePath()
	{
		return $this->user_orgu;
	}
}
