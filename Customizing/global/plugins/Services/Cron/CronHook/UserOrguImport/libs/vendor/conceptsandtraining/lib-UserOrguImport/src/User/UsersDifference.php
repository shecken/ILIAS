<?php

namespace CaT\UserOrguImport\User;

use CaT\UserOrguImport\Item\ItemCollectionDifference as ItemCollectionDifference;

class UsersDifference extends ItemCollectionDifference
{
	public function __construct(Users $left, Users $right)
	{
		$this->createDiff($left, $right);
	}
}
