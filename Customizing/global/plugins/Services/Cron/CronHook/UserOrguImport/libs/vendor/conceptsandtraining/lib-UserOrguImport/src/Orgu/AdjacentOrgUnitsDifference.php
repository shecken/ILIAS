<?php

namespace CaT\UserOrguImport\Orgu;

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;
use CaT\UserOrguImport\Item\Item as Item;
use CaT\UserOrguImport\Item\ItemCollectionDifference as ItemCollectionDifference;

class AdjacentOrgUnitsDifference extends ItemCollectionDifference
{

	protected $ident;

	public function __construct(AdjacentOrgUnits $left, AdjacentOrgUnits $right, Identifier $ident)
	{
		$this->ident = $ident;
		$this->createDiff($left, $right);
	}

	protected function containsChanges(Item $left, Item $right)
	{
		if (!$this->ident->same(
			new OrgUnit($left->parentOrguIdProperties(), $this->ident),
			new OrgUnit($right->parentOrguIdProperties(), $this->ident)
		)) {
			return true;
		}
		return parent::containsChanges($left, $right);
	}

	protected function handleChanges(Item $left, Item $right)
	{
		parent::handleChanges($left->withParentOrguIdProperties($right->parentOrguIdProperties()), $right);
	}
}
