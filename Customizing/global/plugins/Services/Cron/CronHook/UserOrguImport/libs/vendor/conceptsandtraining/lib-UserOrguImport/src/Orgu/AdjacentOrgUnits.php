<?php

namespace CaT\UserOrguImport\Orgu;

use CaT\UserOrguImport\Item\ItemCollection as ItemCollection;

class AdjacentOrgUnits extends ItemCollection
{

	/**
	 * @param	OrgUnit	$orgu
	 * @return void
	 */
	public function add(AdjacentOrgUnit $orgu)
	{
		parent::addItem($orgu);
	}

	public function contains(AdjacentOrgUnit $orgu)
	{
		return parent::containsItem($orgu);
	}
}
