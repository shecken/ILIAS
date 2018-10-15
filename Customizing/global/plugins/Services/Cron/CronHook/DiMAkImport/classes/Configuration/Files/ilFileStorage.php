<?php

namespace CaT\Plugins\DiMAkImport\Configuration\Files;

class ilFileStorage implements FileStorage
{
	const KEY_FILE_PATH = "dimak_source_file_path";
	/**
	 * @var ilSetting
	 */
	protected $settings;

	public function __construct(\ilSetting $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @param string 	$path
	 * @return void
	 */
	public function save($path)
	{
		assert('is_string($path)');
		$this->settings->set(self::KEY_FILE_PATH, $path);
	}

	/**
	 * @return FileConfig
	 */
	public function read()
	{
		return new FileConfig($this->settings->get(self::KEY_FILE_PATH));
	}
}