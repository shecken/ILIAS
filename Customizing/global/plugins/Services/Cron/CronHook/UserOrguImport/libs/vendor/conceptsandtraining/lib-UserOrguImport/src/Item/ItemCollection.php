<?php

namespace CaT\UserOrguImport\Item;

abstract class ItemCollection implements \Iterator
{
	protected $identifier;

	protected $items = [];

	public function __construct(ItemIdentifier $identifier)
	{
		$this->identifier = $identifier;
	}

	public function identifier()
	{
		return $this->identifier;
	}

	public function getEmptyContainer()
	{
		return new static($this->identifier);
	}

	protected function addItem(Item $item)
	{
		$digested = $this->identifier->digestId($item);
		$this->items[$digested] = $item;
	}

	protected function containsItem(Item $item)
	{
		$digested = $this->identifier->digestId($item);
		if (isset($this->items[$digested])) {
			return true;
		}
		if (!$this->identifier->digestUnique()) {
			foreach ($this->items as $digest => $item_c) {
				if ($this->identifier->same($item_c, $item)) {
					return true;
				}
			}
		}
		return false;
	}


	public function rewind()
	{
		reset($this->items);
	}

	public function current()
	{
		return current($this->items);
	}

	public function key()
	{
		return key($this->items);
	}

	public function next()
	{
		next($this->items);
	}

	public function valid()
	{
		return key($this->items) !== null;
	}

	public function itemByIdDigest($digest)
	{
		assert('is_string($digest)');
		return $this->items[$digest];
	}
}
