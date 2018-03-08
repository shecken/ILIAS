<?php

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;
use CaT\UserOrguImport\Item\Item as Item;

class AssignmentTestIdentifier extends Identifier
{
	public function mayBeIdentifiedProperties(array $properties)
	{
		return isset($properties['user']) && isset($properties['role']) && isset($properties['orgu']);
	}

	public function digestId(Item $item)
	{
		$properties = $item->properties();
		return $properties['user'].'_'.$properties['role'].'_'.$properties['orgu'];
	}

	public function same(Item $left, Item $right)
	{
		$p_left = $left->properties();
		$p_right = $right->properties();
		return $p_left['user'] === $p_right['user']
			&& $p_left['role'] === $p_right['role']
			&& $p_left['orgu'] === $p_right['orgu'];
	}

	public function digestUnique()
	{
		return true;
	}
}
