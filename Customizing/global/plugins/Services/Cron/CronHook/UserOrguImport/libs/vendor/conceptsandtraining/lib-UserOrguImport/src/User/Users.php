<?php

namespace CaT\UserOrguImport\User;

use CaT\UserOrguImport\Item\ItemCollection as ItemCollection;

class Users extends ItemCollection
{

	/**
	 * @param	User	$u
	 * @return void
	 */
	public function add(User $u)
	{
		parent::addItem($u);
	}

	public function contains(User $u)
	{
		return parent::containsItem($u);
	}
}
