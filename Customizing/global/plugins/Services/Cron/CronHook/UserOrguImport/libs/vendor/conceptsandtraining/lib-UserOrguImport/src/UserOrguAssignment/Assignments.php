<?php

namespace CaT\UserOrguImport\UserOrguAssignment;
use CaT\UserOrguImport\Item\ItemCollection as ItemCollection;

class Assignments extends ItemCollection
{
	public function add(Assignment $ass)
	{
		parent::addItem($ass);
	}

	public function contains(Assignment $ass)
	{
		return parent::containsItem($ass);
	}
}
