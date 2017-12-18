<?php

namespace CaT\UserOrguImport\Item;

abstract class Item
{
	protected $properties;

	public function __construct(array $properties, ItemIdentifier $ident)
	{
		if (!$ident->mayBeIdentifiedProperties($properties)) {
			throw new \LogicException('may not build object with given properties '.print_r($properties, true));
		}
		$this->properties = $properties;
	}

	public function properties()
	{
		return $this->properties;
	}

	public function withProperties(array $properties)
	{
		$other = clone $this;
		$other->properties = $properties;
		return $other;
	}
}
