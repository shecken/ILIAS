<?php

namespace CaT\UserOrguImport\Orgu;

interface OrgStructure
{
	/**
	 * Insert an org-unit into the under some orgu, which may be root or non-root.
	 *
	 * @param	OrgUnit	$ou
	 * @return	void
	 */
	public function addOrgu(AdjacentOrgUnit $ou);

	/**
	 * Get an org-unit with corresponding id in tree
	 *
	 * @param	string[string]	$id
	 * @return	OrgUnit|null
	 */
	public function orgu(array $id);

	/**
	 * Get all root org units of tree
	 *
	 * @return	OrgUnit[]
	 */
	public function rootOrgus();

	/**
	 * Get all root org units of tree. Throws if structure inconsistent.
	 *
	 * @throws	LogicException
	 * @param	string	$id
	 * @return	OrgUnit[]
	 */
	public function subOrgus(OrgUnit $orgu);

	/**
	 * Check org structure for consistency.
	 *
	 * @return bool
	 */
	public function treeConsistent();
}
