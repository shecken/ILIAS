<?php

namespace CaT\IliasUserOrguImport\User;

use CaT\UserOrguImport\User as User;
use CaT\UserOrguImport\Item as Item;

class IliasUser extends User\User
{

	protected $id;

	public function __construct(array $properties, Item\ItemIdentifier $ident, $id)
	{
		assert('is_int($id)');
		$this->id = $id;
		parent::__construct($properties, $ident);
	}

	/**
	 * The user_id of the ilias user represented by this.
	 *
	 * @return	int
	 */
	public function iliasId()
	{
		return $this->id;
	}
}
