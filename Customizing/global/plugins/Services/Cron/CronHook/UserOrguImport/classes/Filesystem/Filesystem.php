<?php

namespace CaT\IliasUserOrguImport\Filesystem;

require_once 'Services/FileSystem/classes/class.ilFileSystemStorage.php';

class Filesystem extends \ilFileSystemStorage
{

	protected $deployment_path;

	/**
	 * Avoids confusion at object initialisation.
	 */
	public static function getInstance()
	{
		return new static(self::STORAGE_DATA,false,0);
	}

	public function __construct($a_storage_type, $a_path_conversion, $a_container_id)
	{
		parent::__construct($a_storage_type, $a_path_conversion, $a_container_id);
		if (!is_dir($this->getAbsolutePath())) {
			$this->create();
		}
	}

	/**
	 * Move file from $from to $to.
	 *
	 * @param	string	$from
	 * @param	string	$to
	 */
	public function move($from, $to)
	{
		assert('is_string($from)');
		assert('is_string($to)');
		return rename($from, $to);
	}

	/**
	 * @inheritdoc
	 */
	public function getAbsolutePath()
	{
		return $this->canonicDirectoryPath(parent::getAbsolutePath());
	}

	protected function canonicDirectoryPath($path)
	{
		assert('is_string($path)');
		$path = trim(rtrim($path, DIRECTORY_SEPARATOR));
		return $path;
	}

	/**
	 * Get contents of dir besides . and .. .
	 *
	 * @param	string	$dir
	 * @return	string[]
	 */
	public function readDir($dir)
	{
		assert('is_string($dir)');
		$dir_res = opendir($this->canonicDirectoryPath($dir));
		$files = [];
		while (false !== ($entry = readdir($dir_res))) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$files[] = $entry;
		}
		closedir($dir_res);
		return $files;
	}

	protected function getPathPostfix()
	{
		return 'Files';
	}

	protected function getPathPrefix()
	{
		return 'UserOrguImport';
	}

	/**
	 * Check wether a directory is empty.
	 *
	 * @param	string	$dir
	 * @return	bool
	 */
	public function isEmpty($dir)
	{
		$files = $this->readDir($dir);

		return (count($files) == 0) ? true : false;
	}

	/**
	 * Create directory structure for file storage.
	 *
	 * @return true
	 */
	public function create()
	{
		if (!file_exists($this->getAbsolutePath())) {
			\ilUtil::makeDirParents($this->getAbsolutePath());
		}
		return true;
	}
}
