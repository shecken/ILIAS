<?php

namespace CaT\Plugins\DiMAkImport\Configuration\Files;

class FileActions
{
	/**
	 * @var FileStorage
	 */
	protected $storage;

	public function __construct(FileStorage $storage)
	{
		$this->storage = $storage;
	}
	/**
	 * @param string 	$path
	 * @return void
	 */
	public function save($path)
	{
		assert('is_string($path)');
		$this->storage->save($path);
	}

	/**
	 * @return FileConfig
	 */
	public function read()
	{
		return $this->storage->read();
	}
}