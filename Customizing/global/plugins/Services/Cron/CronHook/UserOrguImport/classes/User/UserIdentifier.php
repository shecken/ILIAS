<?php

#DONE

namespace CaT\IliasUserOrguImport\User;

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;
use CaT\UserOrguImport\Item\Item as Item;

class UserIdentifier extends Identifier
{

	/**
	 * @inheritdoc
	 */
	public function mayBeIdentifiedProperties(array $properties)
	{
		return	trim((string)$properties[UdfWrapper::PROP_PNR]) !== '';
	}

	/**
	 * @inheritdoc
	 */
	public function same(Item $left, Item $right)
	{
		$lprop = $left->properties();
		$rprop = $right->properties();
		return	$lprop[UdfWrapper::PROP_PNR] === $rprop[UdfWrapper::PROP_PNR];
	}


	/**
	 * @inheritdoc
	 */
	public function digestUnique()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function digestId(Item $item)
	{
		$prop = $item->properties();
		return $prop[UdfWrapper::PROP_PNR];
	}
}
