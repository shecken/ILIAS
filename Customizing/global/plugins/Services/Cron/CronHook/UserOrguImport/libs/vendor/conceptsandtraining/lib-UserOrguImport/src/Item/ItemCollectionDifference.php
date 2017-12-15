<?php

namespace CaT\UserOrguImport\Item;

abstract class ItemCollectionDifference
{

	protected $changed;
	protected $create;
	protected $delete;

	protected function createDiff(ItemCollection $left, ItemCollection $right)
	{
		assert('get_class($left->identifier()) === get_class($right->identifier())');
		$this->changed = $left->getEmptyContainer();
		$this->create = $left->getEmptyContainer();
		$this->delete = $left->getEmptyContainer();
		$identifier = $left->identifier();
		$unique_digest_id = $identifier->digestUnique();
		foreach ($left as $id_digest_l => $item_l) {
			$item_r = $right->itemByIdDigest($id_digest_l);
			if ($item_r !== null) {
				$this->handlePossibleChanged($item_l, $item_r);
				continue;
			}
			if (!$unique_digest_id) {
				foreach ($right as $id_digest_r => $item_r) {
					if ($identifier->same($item_l, $item_r)) {
						$this->handlePossibleChanged($item_l, $item_r);
						continue 2;
					}
				}
			}
			$this->handleDelete($item_l);
		}
		foreach ($right as $id_digest_r => $item_r) {
			$item_l = $left->itemByIdDigest($id_digest_r);
			if ($item_l !== null) {
				continue;
			}
			foreach ($left as $id_digest_l => $item_l) {
				if ($identifier->same($item_l, $item_r)) {
					continue 2;
				}
			}
			$this->handleCreate($item_r);
		}
	}

	protected function handlePossibleChanged(Item $left, Item $right)
	{
		if ($this->containsChanges($left, $right)) {
			$this->handleChanges($left, $right);
		}
	}

	protected function containsChanges(Item $left, Item $right)
	{
		$properties_l = $left->properties();
		$properties_r = $right->properties();
		foreach ($properties_r as $key => $value) {
			if ($properties_l[$key] !== $value) {
				return true;
			}
		}
		return false;
	}

	protected function handleChanges(Item $left, Item $right)
	{

		$this->changed->add($left->withProperties(array_merge($left->properties(), $right->properties())));
	}

	protected function handleCreate(Item $item)
	{
		$this->create->add($item);
	}

	protected function handleDelete(Item $item)
	{
		$this->delete->add($item);
	}

	public function toCreate()
	{
		return $this->create;
	}

	public function toChange()
	{
		return $this->changed;
	}

	public function toDelete()
	{
		return $this->delete;
	}
}
