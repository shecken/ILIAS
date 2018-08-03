<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

class CountField extends T\DerivedField  {

	protected $distinct;
	protected $arg;

	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field, $distinct = false) {
		$this->derived_from[] = $field;
		$this->distinct = $distinct;
		$this->arg = $field;
		parent::__construct($f, $name);
	}


	public function argument() {
		return $this->arg;
	}

	public function distinct()
	{
		return $this->distinct;
	}

}
