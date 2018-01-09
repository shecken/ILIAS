<?php

/**
 * General plugin factory. Also creates sub-namespace-factories.
 */

namespace CaT\IliasUserOrguImport;

use CaT\UserOrguImport as UOI;

class Factory
{
	/**
	 * @param	\ilDB	$db
	 * @param	\ilRbacAdmin	$rbacadmin
	 * @param	\ilRbacReview	$rbacreview
	 * @param	\ilSetting	$settings
	 * @param	\ilOrgUnitTree	$org_unit_tree
	 * @param	\ilTree	$tree
	 * @param	ErrorReporting\ErrorCollection  $error_collection
	 * @param	\ilLog
	 */
	public function __construct(
		\ilDB $db,
		\ilRbacAdmin $rbacadmin,
		\ilRbacReview $rbacreview,
		\ilSetting $settings,
		\ilObjOrgUnitTree $org_unit_tree,
		\ilTree $tree,
		\ilRepUtil $rep_utils,
		ErrorReporting\ErrorCollection $error_collection,
		\ilLog $illog
	) {

		$this->db = $db;
		$this->rbacadmin = $rbacadmin;
		$this->rbacreview = $rbacreview;
		$this->settings = $settings;
		$this->org_unit_tree = $org_unit_tree;
		$this->tree = $tree;
		$this->rep_utils = $rep_utils;
		$this->error_collection = $error_collection;
		$this->illog = $illog;
	}

	/**
	 * Creates a factory for the User subnamespace.
	 *
	 * @return	User\DEVKUserFactory
	 */
	public function UserFactory()
	{
		return new User\UserFactory(
			$this,
			$this->db,
			$this->settings,
			$this->ImportFiles(),
			$this->DataExtractor(),
			$this->error_collection,
			$this->Log()
		);
	}

	/**
	 * Creates a factory for the Orgu subnamespace.
	 *
	 * @return	Orgu\DEVKOrguFactory
	 */
	public function OrguFactory()
	{
		return new Orgu\OrguFactory(
			$this,
			$this->db,
			$this->settings,
			$this->org_unit_tree,
			$this->tree,
			$this->rep_utils,
			$this->ImportFiles(),
			$this->DataExtractor(),
			$this->error_collection,
			$this->Log()
		);
	}

	/**
	 * Creates a factory for the UserOrguAssignments subnamespace.
	 *
	 * @return	UserOrguAssignments\DEVKUserOrguFactory
	 */
	public function UserOrguAssignmentsFactory()
	{
		return new UserOrguAssignments\UserOrguFactory(
			$this,
			$this->db,
			$this->tree,
			$this->org_unit_tree,
			$this->UserFactory()->UdfWrapper(),
			$this->UserFactory()->UserIdentifier(),
			$this->OrguFactory()->OrguConfig(),
			$this->ImportFiles(),
			$this->DataExtractor(),
			$this->UserFactory()->UserRoleUpdater(),
			$this->UserFactory()->RoleConfiguration(),
			$this->UserFactory()->UserLocator(),
			$this->error_collection,
			$this->Log()
		);
	}

	/**
	 * Create filesystem instance.
	 *
	 * @return	Filesystem\Filesystem
	 */
	public function Filesystem()
	{
		return Filesystem\Filesystem::getInstance();
	}

	/**
	 * Get an ImportFiles instance.
	 *
	 * @return	Filesystem\ImportFiles
	 */
	public function ImportFiles()
	{
		return new Filesystem\ImportFiles($this->Filesystem(), $this->error_collection, $this->FilesystemConfig());
	}

	/**
	 * Get a FilesystemConfig instance.
	 *
	 * @return	Filesystem\FilesystemConfig
	 */
	public function FilesystemConfig()
	{
		return new Filesystem\FilesystemConfig($this->settings);
	}


	/**
	 * Get a  XLSXExtractor instance.
	 *
	 * @return	Excel\XLSXExtractor
	 */
	public function DataExtractor()
	{
		return new Data\SpoutXLSXExtractor();
	}

	/**
	 * Get a  Logr instance.
	 *
	 * @return	Log\DatabaseLog
	 */
	public function Log()
	{
		return new Log\DatabaseLog($this->db, $this->illog);
	}

	/**
	 * Get the ilias role assignment wrapper
	 *
	 * @return	IliasGlobalRoleManagement
	 */
	public function IliasGlobalRoleManagement()
	{
		return new IliasGlobalRoleManagement($this->rbacadmin, $this->rbacreview);
	}

	public function ExitUserManagement()
	{
		return new ExitUserManagement(
			$this->IliasGlobalRoleManagement(),
			$this->OrguFactory()->OrguConfig(),
			$this->UserFactory()->UserLocator(),
			$this->UserFactory()->UdfWrapper(),
			$this->org_unit_tree,
			$this->Log(),
			$this->error_collection
		);
	}
}
