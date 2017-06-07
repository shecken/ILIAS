<?php

namespace CaT\Plugins\ReportMaster\ReportDiscovery;

class MenuItemCollection implements \Iterator
{
	protected $menu_items = [];
	protected $cnt = 0;

	public function withMenuItem(MenuItem $item)
	{
		$other = clone $this;
		$other->menu_items[] = $item;
		return $other;
	}

	public function rewind()
	{
		reset($this->menu_items);
		$this->cnt = 0;
	}

	public function current()
	{
		return current($this->menu_items);
	}

	public function key()
	{
		return key($this->menu_items);
	}

	public function next()
	{
		next($this->menu_items);
		$this->cnt++;
	}

	public function valid()
	{
		return count($this->menu_items) > $this->cnt;
	}

	public function sortByTitle()
	{
		usort($this->menu_items, function ($a, $b) {
					return strcasecmp($a->title(), $b->title());
		});
	}
}
