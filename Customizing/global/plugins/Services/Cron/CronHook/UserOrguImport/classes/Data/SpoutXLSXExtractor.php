<?php

namespace CaT\IliasUserOrguImport\Data;

use Box\Spout\Reader\AbstractReader;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Extract data from an xlsx-file
 */

class SpoutXLSXExtractor extends SpoutExtractor
{
	protected function type()
	{
		return Type::XLSX;
	}
	
	protected function setOptions(AbstractReader $reader)
	{
		return $reader;
	}
}

