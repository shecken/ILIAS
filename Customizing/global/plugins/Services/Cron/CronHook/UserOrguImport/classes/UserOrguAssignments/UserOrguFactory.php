<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

use CaT\IliasUserOrguImport\User as User;
use CaT\IliasUserOrguImport\Orgu as Orgu;
use CaT\IliasUserOrguImport as Base;
use CaT\UserOrguImport as UOI;

/**
 * Factory for any UserOrguAssignments objects.
 */
class UserOrguFactory
{

	/**
	 * @param	\ilDB	$db
	 * @param	\ilTree	$tree
	 * @param	\ilOrgUnitTree	$orgu_tree
	 * @param	\ilSetting $settings
	 * @param 	User\UdfWrapper	$udf
	 * @param 	User\UserIdentifier	$u_ident
	 * @param 	Orgu\OrguConfig	$o_cfg
	 * @param 	Base\Filesystem\ImportFiles	$import_files
	 * @param 	Base\Data\DataExtractor	$data
	 * @param 	User\UserRoleUpdater	$uru
	 * @param 	User\RoleConfiguration	$rc
	 * @param 	User\UserLocator	$u_loc
	 * @param 	Base\ErrorReporting\ErrorCollection	$error_collection
	 * @param 	Base\Log\Log	$log
	*/
	public function __construct(
		Base\Factory $f,
		$db,
		$tree,
		$orgu_tree,
		\ilSetting $settings,
		User\UdfWrapper $udf,
		User\UserIdentifier $u_ident,
		Orgu\OrguConfig $o_cfg,
		Base\Filesystem\ImportFiles $import_files,
		Base\Data\DataExtractor $data,
		User\UserRoleUpdater $uru,
		User\RoleConfiguration $rc,
		User\UserLocator $u_loc,
		Base\ErrorReporting\ErrorCollection $error_collection,
		Base\Log\Log $log
	) {
		$this->f = $f;
		$this->db = $db;
		$this->tree = $tree;
		$this->orgu_tree = $orgu_tree;
		$this->settings = $settings;
		$this->udf = $udf;
		$this->u_ident = $u_ident;
		$this->o_cfg = $o_cfg;
		$this->import_files = $import_files;
		$this->data = $data;
		$this->uru = $uru;
		$this->rc = $rc;
		$this->u_loc = $u_loc;
		$this->error_collection = $error_collection;
		$this->log = $log;
	}

	/**
	 * Get the ilias side locator for user orgu assignments
	 *
	 * @return	UserOrguLocator
	 */
	public function UserOrguLocator()
	{
		return new UserOrguLocator(
			$this->db,
			$this->tree,
			$this->orgu_tree,
			$this->UserOrguIdentifier(),
			$this->udf,
			$this->o_cfg,
			$this->u_loc
		);
	}

	/**
	 * Get the ilias side updater for user orgu assignments
	 *
	 * @return	UserOrguUpdater
	 */
	public function UserOrguUpdater()
	{
		return new UserOrguUpdater(
			$this->UserOrguLocator(),
			$this->UserOrguFunctionConfigDB(),
			$this->f->IliasGlobalRoleManagement(),
			$this->udf,
			$this->error_collection,
			$this->log
		);
	}

	/**
	 * Get the identifier for user orgu assignments
	 *
	 * @return	UserOrguIdentifier
	 */
	public function UserOrguIdentifier()
	{
		return new UserOrguIdentifier();
	}

	/**
	 * Get the excel parser to extract designated user orgu assignemnt.
	 *
	 * @return	UserOrguExcel
	 */
	public function UserOrguExcel()
	{
		return new UserOrguExcel(
			$this->UserOrguLocator(),
			$this->UserOrguFunctionConfigDB()->load(),
			$this->import_files,
			$this->data,
			$this->UserOrguIdentifier(),
			$this->f->UserFactory()->UserIdentifier(),
			$this->error_collection
		);
	}

	/**
	 * Get the storage for the global function role assignment storage.
	 *
	 * @return	UserOrguFunctionConfigDB
	 */
	public function UserOrguFunctionConfigDB()
	{
		return new UserOrguFunctionConfigDB($this->db, $this->settings);
	}

	/**
	 * Get the difference of ilias- and excel-side assignments.
	 *
	 * @param	UOI\UserOrguAssignment\Assignments	$left
	 * @param	UOI\UserOrguAssignment\Assignments	$right
	 * @return	UOI\UserOrguAssignment\AssignmentsDifference
	 */
	public function Difference(UOI\UserOrguAssignment\Assignments $left, UOI\UserOrguAssignment\Assignments $right)
	{
		return new UOI\UserOrguAssignment\AssignmentsDifference($left, $right);
	}
}
