<?php

namespace CaT\UserOrguImport\UserOrguAssignment;

use CaT\UserOrguImport\Item\ItemCollectionDifference as ItemCollectionDifference;

class AssignmentsDifference extends ItemCollectionDifference
{
	public function __construct(Assignments $left, Assignments $right)
	{
		$this->createDiff($left, $right);
	}
}
