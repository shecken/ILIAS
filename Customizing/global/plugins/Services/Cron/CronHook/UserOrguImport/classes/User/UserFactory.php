<?php

namespace CaT\IliasUserOrguImport\User;

use CaT\IliasUserOrguImport as Base;
use CaT\UserOrguImport as UOI;

/**
 * Factory for any User objects.
 */
class UserFactory
{

	protected $db;

	/**
	 * @param	\ilDB	$db
	 * @param	\ilRbacAdmin	$rbacadmin
	 * @param	\ilRbacReview	$rbacreview
	 * @param	\ilSetting	$settings
	 * @param	Base\Filesystem\ImportFiles	$import_files
	 * @param 	Base\Data\DataExtractor	$data
	 * @param 	Base\ErrorReporting\ErrorCollection	$error_collection
	 * @param 	Base\Log\Log	$log
	*/
	public function __construct(
		Base\Factory $f,
		$db,
		\ilSetting $settings,
		Base\Filesystem\ImportFiles $import_files,
		Base\Data\DataExtractor $data,
		Base\ErrorReporting\ErrorCollection $error_colletion,
		Base\Log\Log $log
	) {
		$this->f = $f;
		$this->db = $db;
		$this->settings = $settings;
		$this->import_files = $import_files;
		$this->data = $data;
		$this->error_colletion = $error_colletion;
		$this->log = $log;
	}

	/**
	 * Get an instance of the User config storage.
	 *
	 * @return	UserConfig
	 */
	public function UserConfig()
	{
		return new  UserConfig($this->settings);
	}

	/**
	 * Get an instance of the udf wrapper.
	 *
	 * @return	UdfWrapper
	 */
	public function UdfWrapper()
	{
		return new UdfWrapper($this->UserConfig(), $this->db);
	}

	/**
	 * Get the identifier for users
	 *
	 * @return	UserIdentifier
	 */
	public function UserIdentifier()
	{
		return new UserIdentifier();
	}

	/**
	 * Get the ilias side locator for users
	 *
	 * @return	UserLocator
	 */
	public function UserLocator()
	{
		return new UserLocator($this->UserIdentifier(), $this->UdfWrapper(), $this->db);
	}



	/**
	 * Get the ilias role assignment configurration
	 *
	 * @return	RoleConfiguration
	 */
	public function RoleConfiguration()
	{
		return new RoleConfiguration($this->db, $this->f->IliasGlobalRoleManagement());
	}

	/**
	 * Get the ilias side updater for users
	 *
	 * @return	DEVKUserRoleUpdater
	 */
	public function UserRoleUpdater()
	{
		return new UserRoleUpdater($this->f->IliasGlobalRoleManagement(), $this->log);
	}

	/**
	 * Get the excel parser to extract designated user data
	 *
	 * @return	ExcelUsers
	 */
	public function ExcelUsers()
	{
		return new ExcelUsers($this->import_files, $this->data, $this->UserIdentifier(), $this->error_colletion);
	}

	/**
	 * Get the ilias side updater for users
	 *
	 * @return	UserUpdater
	 */
	public function UserUpdater()
	{
		return new UserUpdater($this->UserLocator(), $this->UdfWrapper(), $this->error_colletion, $this->log);
	}

	/**
	 * Get the difference of ilias- and excel-side assignments.
	 *
	 * @param	UOI\User\Users	$left
	 * @param	UOI\User\Users	$right
	 * @return	 UOI\User\UsersDifference
	 */
	public function Difference(UOI\User\Users $left, UOI\User\Users $right)
	{
		return new UOI\User\UsersDifference($left, $right);
	}
}
