<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

use CaT\UserOrguImport\UserOrguAssignment as Assignment;
use CaT\UserOrguImport\Item as Item;

class UserOrguAssignment extends Assignment\Assignment
{

	const ILIAS_SUPERIOR = 'superior';
	const ILIAS_EMPLOYEE = 'employee';

	public function __construct(array $properties, Item\ItemIdentifier $ident, $ilias_user_id, $ilias_orgu_reference, $ilias_role)
	{
		assert('is_int($ilias_user_id)');
		assert('is_int($ilias_orgu_reference)');
		assert('self::ILIAS_EMPLOYEE === $ilias_role || self::ILIAS_SUPERIOR === $ilias_role');
		parent::__construct($properties, $ident);
		$this->ilias_user_id = $ilias_user_id;
		$this->ilias_orgu_reference = $ilias_orgu_reference;
		$this->ilias_role = $ilias_role;
	}

	/**
	 * Get the ilias user_id of the corresponding assignment.
	 *
	 * @return	int
	 */
	public function iliasUserId()
	{
		return $this->ilias_user_id;
	}

	/**
	 * Get the ilias orgu ref_id of the corresponding assignment.
	 *
	 * @return	int
	 */
	public function iliasOrguRefId()
	{
		return $this->ilias_orgu_reference;
	}

	/**
	 * Get the ilias role_id of the corresponding assignment.
	 *
	 * @return	int
	 */
	public function iliasRole()
	{
		return $this->ilias_role;
	}
}
