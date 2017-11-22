<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Group concat all the entries in a field.
 */
class GroupConcat extends T\DerivedField  {
	protected $separator;
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field, $separator = ', ') {
		$this->derived_from[] = $field;
		$this->separator = $separator;
		$this->arg = $field;
		parent::__construct($f, $name);
	}

	/**
	 * The field being concat.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}

	/**
	 * Concat using a separator.
	 *
	 * @return 	string
	 */
	public function separator() {
		return $this->separator;
	}
}
