<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Form the product between two fieldentries in the same row.
 */
class Times extends T\DerivedField {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $left, Filters\Predicates\Field $right) {
		$this->derived_from[] = $left;
		$this->derived_from[] = $right;
		$this->left = $left;
		$this->right = $right;
		parent::__construct($f, $name);
	}

	/**
	 * First factor field.
	 * @return AbstractField
	 */
	public function left() {
		return $this->left;
	}

	/**
	 * Second factor field.
	 * @return AbstractField
	 */
	public function right() {
		return $this->right;
	}
}