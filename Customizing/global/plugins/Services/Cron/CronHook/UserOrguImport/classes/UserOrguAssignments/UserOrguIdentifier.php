<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

use CaT\UserOrguImport\Item as Item;
use CaT\IliasUserOrguImport\User\UdfWrapper as Udf;
use CaT\IliasUserOrguImport\Orgu\OrguAMDWrapper as AMD;

class UserOrguIdentifier extends Item\ItemIdentifier
{

	const ROLE_SUPERIOR = 'role_superior';
	const ROLE_EMPLOYEE = 'role_employee';

	/**
	 * @inheritdoc
	 */
	public function mayBeIdentifiedProperties(array $properties)
	{
		$user_id_set = isset($properties[Udf::PROP_PNR]) && trim((string)$properties[Udf::PROP_PNR]) !== '';
		$orgu_id_set = isset($properties[AMD::PROP_ID]) && trim((string)$properties[AMD::PROP_ID]) !== '';
		$definite_role = trim((string)$properties[UserOrguAMDWrapper::PROP_ROLE]) === self::ROLE_SUPERIOR
			|| trim((string)$properties[UserOrguAMDWrapper::PROP_ROLE]) === self::ROLE_EMPLOYEE;
		return $user_id_set && $orgu_id_set && $definite_role;
	}

	/**
	 * @inheritdoc
	 */
	public function same(Item\Item $left, Item\Item $right)
	{
		return $this->digestId($left) === $this->digestId($right);
	}


	/**
	 * @inheritdoc
	 */
	public function digestUnique()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function digestId(Item\Item $item)
	{
		$properties =  $item->properties();
		$user_id = (string)$properties[Udf::PROP_PNR];
		$org_id = (string)$properties[AMD::PROP_ID];
		$role = $properties[UserOrguAMDWrapper::PROP_ROLE];
		return $user_id.'_'.$org_id.'_'.$role;
	}
}
