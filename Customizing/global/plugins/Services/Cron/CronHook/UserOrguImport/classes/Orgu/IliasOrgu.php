<?php

namespace CaT\IliasUserOrguImport\Orgu;

use CaT\UserOrguImport\Orgu as Orgu;
use CaT\UserOrguImport\Item as Item;

class IliasOrgu extends Orgu\AdjacentOrgUnit
{
	protected $id;

	public function __construct(array $properties, Item\ItemIdentifier $ident, array $parent_properties, $id)
	{
		assert('is_int($id)');
		$this->id = $id;
		parent::__construct($properties, $ident, $parent_properties);
	}

	/**
	 * Get the ref_id of an org-unit represented by this object.
	 *
	 * @return	int
	 */
	public function refId()
	{
		return $this->id;
	}
}
