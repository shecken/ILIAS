<?php

namespace CaT\IliasUserOrguImport\Orgu;

use CaT\UserOrguImport\Orgu as Orgu;
use CaT\UserOrguImport\Item\ItemIdentifier as ItemIdentifier;

/**
 * Locates relevant org units within ilias.
 */
class OrguLocator
{

	public function __construct(OrguConfig $oc, OrguAMDWrapper $amd, $orgu_tree, $tree, ItemIdentifier $identifier)
	{
		$this->oc = $oc;
		$this->root_ref = $oc->getRootRefId();
		$this->identifier = $identifier;
		$this->orgu_tree = $orgu_tree;
		$this->tree = $tree;
		$this->amd = $amd;
	}

	/**
	 * Get the orgu by imprort id from ilias.
	 *
	 * @param	string	$id
	 * @return	IliasOrgu
	 */
	public function orguById($id)
	{
		assert('is_string($id)');
		if ($id === OrguConfig::ROOT_ID) {
			$ref_id = $this->root_ref;
			return new IliasOrgu([OrguAMDWrapper::PROP_ID => OrguConfig::ROOT_ID], $this->identifier, [OrguAMDWrapper::PROP_ID => OrguConfig::ROOT_ID], (int)$ref_id);
		} else {
			$obj_id = \ilObject::_lookupObjIdByImportId($id);
			if (!$obj_id) {
				return null;
			}
			if (\ilObject::_lookupType($obj_id) !== 'orgu') {
				throw new \InvalidArgumentException($id.' does not belong to an org unit');
			}
			$ref_id = array_shift(\ilObject::_getAllReferences($obj_id));
			return new IliasOrgu($this->getPropertiesByRefId($ref_id), $this->identifier, $this->getParentIdPropertiesByRefId($ref_id), (int)$ref_id);
		}
	}

	/**
	 * Ilias-ref-id by import id.
	 *
	 * @param string $import_id
	 * @return int
	 */
	public function refIdByImportId($import_id)
	{
		assert('is_string($import_id)');
		return current(\ilObject::_getAllReferences(\ilObject::_lookupObjIdByImportId($import_id)));
	}

	/**
	 * All relevant orgus within import subtree.
	 *
	 * @return	Orgu\AdjacentOrgUnits
	 */
	public function getRelevantOrgus()
	{
		$root_id = $this->oc->getRootRefId();
		return $this->getSubtreeOrgus($root_id);
	}

	/**
	 * Get all orgus in subtree.
	 *
	 * @return	Orgu\AdjacentOrgUnits
	 */
	protected function getSubtreeOrgus($root_ref_id)
	{
		$orgus = new Orgu\AdjacentOrgUnits($this->identifier);
		$exit_subtree = $this->tree->getSubTree($this->tree->getNodeTreeData($this->oc->getExitRefId()), false, 'orgu');
		foreach ($this->tree->getSubTree($this->tree->getNodeTreeData($root_ref_id), false, 'orgu') as $ref_id) {
			if ($ref_id != $root_ref_id && !in_array($ref_id, $exit_subtree)) {
				$props = $this->getPropertiesByRefId((int)$ref_id);
				if (trim((string)$props[OrguAMDWrapper::PROP_ID]) !== '') {
					$orgus->add(new IliasOrgu(
						$props,
						$this->identifier,
						$this->getParentIdPropertiesByRefId($ref_id),
						(int)$ref_id
					));
				}
			}
		}
		return $orgus;
	}


	protected function getParentIdPropertiesByRefId($ref_id)
	{
		$parent = (int)$this->tree->getParentNodeData($ref_id)['ref_id'];
		if ($parent === $this->root_ref) {
			return [OrguAMDWrapper::PROP_ID => OrguConfig::ROOT_ID];
		}
		return $this->getPropertiesByRefId($parent);
	}

	/**
	 * Import-relevant properties by ref_id.
	 *
	 * @param	int	$ref_id
	 * @return	mixed[string]
	 */
	public function getPropertiesByRefId($ref_id)
	{
		assert('is_int($ref_id)');
		$orgu = new \ilObjOrgUnit($ref_id);
		$prop = [OrguAMDWrapper::PROP_ID => $orgu->getImportId()
				,OrguAMDWrapper::PROP_TITLE => $orgu->getTitle()];
		return $prop;
	}
}
