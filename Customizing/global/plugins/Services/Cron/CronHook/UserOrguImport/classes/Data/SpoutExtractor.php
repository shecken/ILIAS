<?php

namespace CaT\IliasUserOrguImport\Data;

use Box\Spout\Reader\AbstractReader;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

abstract class SpoutExtractor implements DataExtractor
{
	abstract protected function type();
	abstract protected function setOptions(AbstractReader $reader);

	/**
	 * @inheritdoc
	 */
	public function extractContent($path_to_file, array $field_conversion)
	{
		assert('is_string($path_to_file)');
		$reader = $this->setOptions(ReaderFactory::create($this->type()));
		$reader->open($path_to_file);

		$it = $reader->getSheetIterator();
		$it->rewind();
		$it = $it->current()->getRowIterator();
		$it->rewind();
		$header = $it->current();
		$assignments = [];
		foreach ($field_conversion as $current => $desired) {
			$location = array_search($current, $header);
			if ($location === false) {
				throw new \InvalidArgumentException('can not locate field '.$current.' in file '.$location);
			}
			$assignments[$desired] = $location;
		}
		$cnt = 0;
		$return = [];
		foreach ($it as $row) {
			if ($cnt === 0) {
				$cnt = 1;
				continue;
			}
			$aux = [];
			foreach ($assignments as $field_desired => $position) {
				$aux[$field_desired] = $row[$position];
			}
			if (!$this->rowEmpty($aux)) {
				$return[] = $this->stringify($aux);
			}
		}
		return $return;
	}

	protected function stringify(array $row)
	{
		$return = [];
		foreach ($row as $key => $value) {
			if($value instanceof \DateTime) {
				$return[$key] = $value->format('Y-m-d');
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}

	protected function rowEmpty(array $row)
	{
		foreach ($row as $key => $value) {
			if (is_object($value)) {
				return false;
			}
			if (trim((string)$value) !== '') {
				return false;
			}
		}
		return true;
	}
}