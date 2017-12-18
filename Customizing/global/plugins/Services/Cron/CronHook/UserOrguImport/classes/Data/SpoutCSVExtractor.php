<?php

namespace CaT\IliasUserOrguImport\Data;

use Box\Spout\Reader\AbstractReader;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

class SpoutCSVExtractor extends SpoutExtractor {

	protected $field_delimiter;
	protected $field_enclosure;

	public function __construct($field_delimiter,$field_enclosure = '')
	{
		assert('is_string($field_delimiter)');
		assert('is_string($field_enclosure)');
		$this->field_delimiter = $field_delimiter;
		$this->field_enclosure = $field_enclosure;
	}
	
	protected function type()
	{
		return Type::CSV;
	}
	
	protected function setOptions(AbstractReader $reader)
	{
		$reader->setFieldDelimiter($this->field_delimiter);
		if($this->field_enclosure !== '') {
			$reader->setFieldEnclosure($this->field_enclosure);
		}
		return $reader;
	}

}