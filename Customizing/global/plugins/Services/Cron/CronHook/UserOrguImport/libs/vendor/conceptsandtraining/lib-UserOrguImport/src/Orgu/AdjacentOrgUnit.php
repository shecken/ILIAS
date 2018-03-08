<?php

namespace CaT\UserOrguImport\Orgu;

use CaT\UserOrguImport\Item\ItemIdentifier as Identifier;

class AdjacentOrgUnit extends OrgUnit
{

	protected $parent_id_properties;
	protected $ident;

	public function __construct(array $properties, Identifier $ident, array $parent_id_properties = [])
	{
		parent::__construct($properties, $ident);
		if (count($parent_id_properties) === 0) {
			if (!$ident->mayBeIdentifiedProperties($parent_id_properties)) {
				throw new \LogicException('invalid parent properties given');
			}
		}
		$this->parent_id_properties = $parent_id_properties;
		$this->ident = $ident;
	}

	public function parentOrguIdProperties()
	{
		return $this->parent_id_properties;
	}

	public function withParentOrguIdProperties(array $parent_id_properties)
	{
		if (!$this->ident->mayBeIdentifiedProperties($parent_id_properties)) {
			throw new \LogicException('invalid parent properties given');
		}
		$other = clone $this;
		$other->parent_id_properties = $parent_id_properties;
		return $other;
	}
}
