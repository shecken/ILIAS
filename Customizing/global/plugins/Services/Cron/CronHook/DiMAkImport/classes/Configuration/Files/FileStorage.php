<?php

namespace CaT\Plugins\DiMAkImport\Configuration\Files;

interface FileStorage
{
	/**
	 * @param string 	$path
	 * @return void
	 */
	public function save($path);

	/**
	 * @return FileConfig
	 */
	public function read();
}