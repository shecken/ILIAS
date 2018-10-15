<?php

namespace CaT\Plugins\DiMAkImport\Configuration\Files;

class FileConfig
{
	/**
	 * @var string
	 */
	 protected $path;

	 public function __construct($path)
	 {
	 	assert('is_string($path)');
	 	$this->path = $path;
	 }

	 public function getPath()
	 {
	 	return $this->path;
	 }
}