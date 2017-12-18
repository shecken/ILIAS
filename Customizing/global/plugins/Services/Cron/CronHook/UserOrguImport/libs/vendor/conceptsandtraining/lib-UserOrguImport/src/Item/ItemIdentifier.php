<?php

namespace CaT\UserOrguImport\Item;


/**
 * An should be identified by some subset of its properties.
 * This may not be trivial. For instance:
 * An item may contain TWO ids, of which any is key. Two items
 * are then identified as silblings, if they aggree in any of the
 * ids. It is impossible to create a digest, which gives same digest
 * For any possble combination.
 */

abstract class ItemIdentifier
{

	/**
	 * Does an item contain enough properties, to be identified.
	 *
	 * @return bool
	 */
	public function mayBeIdentified(Item $item)
	{
		return $this->mayBeIdentifiedProperties($item->properties());
	}

	abstract public function mayBeIdentifiedProperties(array $properties);

	/**
	 * Check, whether an an item fits another.
	 *
	 * @return	bool
	 */
	abstract public function same(Item $left, Item $right);


	/**
	 * Is the digestId function of this implementation unique?
	 * Cases may exist, where items identifeid by same may have distinct
	 * digests. If this is not the case fro some item implementation,
	 * digestUnique should return true. This may improve performance in
	 * containers, since the digests may be used as keys for objects.
	 *
	 * @return	bool
	 */
	public function digestUnique()
	{
		return false;
	}

	/**
	 * Digest item id-properties to a string.
	 *
	 * @return	string
	 */
	abstract public function digestId(Item $item);
}
