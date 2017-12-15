<?php

namespace CaT\IliasUOITestObjects;

use CaT\IliasUserOrguImport\Filesystem\Filesystem as FS;

class TestFileSystem extends FS
{
	public $next_move = true;

	public function move($from, $to)
	{
		return $this->next_move;
	}

	public function getAbsolutePath()
	{
		return 'fake';
	}

	public function create()
	{
		return true;
	}
}
