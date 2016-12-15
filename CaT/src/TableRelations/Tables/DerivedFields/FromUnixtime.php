<?php

namespace CaT\TableRelations\Tables\DerivedFields;
use CaT\TableRelations\Tables as T;
use CaT\Filter as Filters;
/**
 * Translate a timestamp into ISO DateTime
 */
class FromUnixtime extends T\DerivedField  {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field) {
		$this->derived_from[] = $field;
		$this->arg = $field;
		parent::__construct($f, $name);
	}

	/**
	 * The transformed field field.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}
}