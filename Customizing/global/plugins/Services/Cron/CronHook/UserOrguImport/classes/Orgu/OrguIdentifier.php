<?php

namespace CaT\IliasUserOrguImport\Orgu;

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;
use CaT\UserOrguImport\Item\Item as Item;

class OrguIdentifier extends Identifier
{

	/**
	 * @inheritdoc
	 */
	public function mayBeIdentifiedProperties(array $properties)
	{
		return	trim((string)$properties[OrguAMDWrapper::PROP_ID]) !== '';
	}

	/**
	 * @inheritdoc
	 */
	public function same(Item $left, Item $right)
	{
		$lprop = $left->properties();
		$rprop = $right->properties();
		return		$lprop[OrguAMDWrapper::PROP_ID] === $rprop[OrguAMDWrapper::PROP_ID]
					&& trim((string)$rprop[OrguAMDWrapper::PROP_ID]) !== ''
					&& trim((string)$lprop[OrguAMDWrapper::PROP_ID]) !== '';
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
		return (string)$item->properties()[OrguAMDWrapper::PROP_ID];
	}
}
