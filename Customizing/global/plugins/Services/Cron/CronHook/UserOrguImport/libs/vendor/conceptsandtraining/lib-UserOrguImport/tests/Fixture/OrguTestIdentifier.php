<?php

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;
use CaT\UserOrguImport\Item\Item as Item;

class OrguTestIdentifier extends Identifier
{

	public function mayBeIdentifiedProperties(array $properties)
	{
		return isset($properties['p1']);
	}

	public function digestId(Item $item)
	{
		return $item->properties()['p1'];
	}

	public function same(Item $left, Item $right)
	{
		return $left->properties()['p1'] ===  $right->properties()['p1'];
	}

	public function digestUnique()
	{
		return true;
	}
}
