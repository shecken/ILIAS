<?php

namespace CaT\IliasUserOrguImport;

class OrgusCleanup
{

	protected $tree;
	protected $rep_utils;
	protected $grm;
	protected $oc;

	public function __construct($tree, $rep_utils, IliasGlobalRoleManagement $grm, Orgu\OrguConfig $oc)
	{
		$this->tree = $tree;
		$this->rep_utils = $rep_utils;
		$this->grm = $grm;
		$this->oc = $oc;
	}

	/**
	 * Delete all import subtree orgus, which have no user assignments and only
	 * contain orgus without user assignments.
	 *
	 * @return void
	 */
	public function cleanupEmptyOrgus()
	{
		$exit_subtree = $this->tree->getSubTree(
			$this->tree->getNodeTreeData($this->oc->getExitRefId()),
			false,
			'orgu'
		);
		$root_ref_id = $this->oc->getRootRefId();
		$delete_candidates = [];
		// all leafs are candidates, since they obviously contain no
		// orgus which have any assignment, so get all leafs first
		foreach ($this->tree->getSubTree($this->tree->getNodeTreeData($root_ref_id), false, 'orgu') as $ref_id) {
			if ($ref_id !== $root_ref_id && !in_array($ref_id, $exit_subtree)) {
				// leafs contain no children
				if (count($this->tree->getChildsByType($ref_id, 'orgu')) === 0) {
					$delete_candidates[] = (int)$ref_id;
				}
			}
		}
		$delete = [];
		while ($candidate = array_shift($delete_candidates)) {
			$orgu = new \ilObjOrgUnit($candidate);
			// a candidate should be deleted, if it has no user assignments
			if (0 === $this->grm->numberOfAssignedUsers([$orgu->getSuperiorRole(),$orgu->getEmployeeRole()])) {
				$delete[] = $candidate;
				// if an orgu is to be deleted, its parent is a delete candidate as well
				$parent = (int)$this->tree->getParentId($candidate);
				if ($parent !== $root_ref_id) { // ignore tree root
					array_push($delete_candidates, $parent);
				}
			}
		}
		$this->remove($delete);
	}

	/**
	 * Remove orgus with provided information.
	 *
	 * @param	int[]	$ref_ids
	 * @return	void
	 */
	public function remove(array $ref_ids)
	{
		$to_delete_rec = [];
		foreach ($ref_ids as $ref_id) {
			foreach ($this->tree->getSubTree($this->tree->getNodeTreeData($ref_id), false, 'orgu') as $c_ref_id) {
				if (!in_array((int)$c_ref_id, $to_delete_rec)) {
					$to_delete_rec[] = (int)$c_ref_id;
				}
			}
		}
		if (count($to_delete_rec) > 0) {
			$to_delete = [];
			foreach ($to_delete_rec as $ref_id) {
				$parent_id = (int)$this->tree->getParentId($ref_id);
				if (!in_array($parent_id, $to_delete_rec)) {
					$to_delete[] = $ref_id;
				}
			}
			$this->rep_utils->deleteObjects($this->oc->getRootRefId(), $to_delete);
		}
	}
}
