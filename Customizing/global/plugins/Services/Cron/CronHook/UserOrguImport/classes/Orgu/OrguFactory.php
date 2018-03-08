<?php

namespace CaT\IliasUserOrguImport\Orgu;

use CaT\IliasUserOrguImport as Base;
use CaT\UserOrguImport as UOI;

/**
 * Factory for any Orgu objects.
 */
class OrguFactory
{

	/**
	 * @param	\ilDB	$db
	 * @param	\ilSettings	$settings
	 * @param	\ilOrgUnitTree	$orgu_tree
	 * @param	\ilTree	$tree
	 * @param 	Base\Filesystem\ImportFiles	$import_files
	 * @param 	Base\Data\DataExtractor	$data
	 * @param 	Base\ErrorReporting\ErrorCollection	$error_collection
	 * @param 	Base\Log\Log	$log
	*/
	public function __construct(
		Base\Factory $f,
		$db,
		$settings,
		$orgu_tree,
		$tree,
		$rep_utils,
		Base\Filesystem\ImportFiles $import_files,
		Base\Data\DataExtractor $data,
		Base\ErrorReporting\ErrorCollection $error_colletion,
		Base\Log\Log $log
	) {
		$this->f = $f;
		$this->db = $db;
		$this->settings = $settings;
		$this->orgu_tree = $orgu_tree;
		$this->tree = $tree;
		$this->rep_utils = $rep_utils;
		$this->import_files = $import_files;
		$this->data = $data;
		$this->error_colletion = $error_colletion;
		$this->log = $log;
	}

	/**
	 * Get the identifier for orgus
	 *
	 * @return	OrguIdentifier
	 */
	public function OrguIdentifier()
	{
		return new OrguIdentifier();
	}

	/**
	 * Get the ilias side locator for orgus
	 *
	 * @return	OrguLocator
	 */
	public function OrguLocator()
	{
		return new OrguLocator(
			$this->OrguConfig(),
			$this->OrguAMDWrapper(),
			$this->orgu_tree,
			$this->tree,
			$this->OrguIdentifier()
		);
	}

	/**
	 * Get the wrapper for ilias-side amd configuration
	 *
	 * @return	OrguAMDWrapper
	 */
	public function OrguAMDWrapper()
	{
		return new OrguAMDWrapper(
			$this->OrguConfig(),
			$this->db
		);
	}

	/**
	 * Get the ilias side updater for orgus
	 *
	 * @return	OrguUpdater
	 */
	public function OrguUpdater()
	{
		return new OrguUpdater(
			$this->OrguLocator(),
			$this->OrguAMDWrapper(),
			$this->OrguConfig(),
			$this->orgu_tree,
			$this->tree,
			$this->rep_utils,
			$this->f->IliasGlobalRoleManagement(),
			$this->error_colletion,
			$this->log
		);
	}

	/**
	 * Get an instance of the orgu config storage.
	 *
	 * @return	OrguConfig
	 */
	public function OrguConfig()
	{
		return new OrguConfig($this->settings);
	}

	/**
	 * Get the excel parser to extract designated orgu data
	 *
	 * @return	ExcelOrgus
	 */
	public function ExcelOrgus()
	{
		return new ExcelOrgus($this->import_files, $this->data, $this->OrguIdentifier(), $this->error_colletion);
	}

	/**
	 * Get the difference of ilias- and excel-side data
	 *
	 * @param	UOI\Orgu\AdjacentOrgUnits	$left
	 * @param	UOI\Orgu\AdjacentOrgUnits	$right
	 * @return	 UOI\Orgu\AdjacentOrgUnitsDifference
	 */
	public function Difference(UOI\Orgu\AdjacentOrgUnits $left, UOI\Orgu\AdjacentOrgUnits $right)
	{
		return new UOI\Orgu\AdjacentOrgUnitsDifference($left, $right, $this->OrguIdentifier());
	}
}
