<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
class EitherType extends Type {
	/**
	 * @var	Type
	 */
	private $left;

	/**
	 * @var	Type
	 */
	private $right;

	public function __construct(Type $left, Type $right) {
		$this->left  = $left;
		$this->right = $right;
	}

	/**
	 * @inheritdocs
	 */
	public function repr() {
		return "(".$this->left->repr()."|".$this->right->repr().")";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		if ($this->left->contains($value)) {
			return true;
		}
		if ($this->right->contains($value)) {
			return true;
		}
		return false;
	}

	/**
	 * @inheritdocs
	 */
	public function unflatten(array &$value) {
		$backup = $value;
		try {
			return $this->left->unflatten($value);
		}
		catch (\InvalidArgumentException $e) {
			// Must be right-type then...
		}
		return $this->right->unflatten($backup);
	}

	/**
	 * @inheritdocs
	 */
	public function flatten($value) {
		if ($this->left->contains($value)) {
			return $this->left->flatten($value);
		}
		assert($this->right->contains($value));
		return $this->right->flatten($value);
	}

}
