<?php

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;
use CaT\UserOrguImport\Item\Item as Item;

class UserTestIdentifier extends Identifier
{
	public function mayBeIdentifiedProperties(array $properties)
	{
		return isset($properties['p1']) || isset($properties['p2']) ;
	}

	public function digestId(Item $item)
	{
		return $item->properties()['p1'].'_'.$item->properties()['p2'];
	}

	public function same(Item $left, Item $right)
	{
		$lp1 = $left->properties()['p1'];
		$lp2 = $left->properties()['p2'];
		$rp1 = $right->properties()['p1'];
		$rp2 = $right->properties()['p2'];
		return ( $lp1 ===  $rp1 && $rp1 !== null && $lp1 !== null)
				|| ( $lp2 ===  $rp2 && $rp2 !== null && $lp2 !== null);
	}

	public function digestUnique()
	{
		return false;
	}
}
