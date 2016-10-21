<?php

namespace CaT\TableRelations\Tables\DerivedFields;
use CaT\TableRelations\Tables as T;
use CaT\Filter as Filters;
class GroupConcat extends T\DerivedField  {
	protected $separator;
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field, $separator = ', ') {
		$this->derived_from[] = $field;
		$this->separator = $separator;
		$this->arg = $field;
		parent::__construct($f, $name);
	}

	public function argument() {
		return $this->arg;
	}

	public function separator() {
		return $this->separator;
	}
}