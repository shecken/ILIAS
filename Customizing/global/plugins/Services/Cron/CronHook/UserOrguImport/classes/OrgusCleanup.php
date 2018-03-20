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
		$member_counter = new RecursiveMemberCounter();



		$exit_subtree = $this->tree->getSubTree(
			$this->tree->getNodeTreeData($this->oc->getExitRefId()),
			false,
			'orgu'
		);
		$root_ref_id = $this->oc->getRootRefId();
		$iter = [];
		foreach ($this->tree->getChildsByType($root_ref_id, 'orgu') as $node_data) {
			$ref_id = $node_data['ref_id'];
			if (!in_array($ref_id, $exit_subtree)) {
				$iter[] = $ref_id;
				$orgu = new \ilObjOrgUnit($ref_id);
				$members = $this->grm->numberOfAssignedUsers([$orgu->getSuperiorRole(),$orgu->getEmployeeRole()]);
				$member_counter->addNode($ref_id, $members);
			}
		}

		while ($ref_id = array_shift($iter)) {
			foreach ($this->tree->getChildsByType($ref_id, 'orgu') as $node_data) {
				$sub_ref_id = $node_data['ref_id'];
				if (!in_array($sub_ref_id, $exit_subtree)) {
					array_push($iter, $sub_ref_id);
					$orgu = new \ilObjOrgUnit($sub_ref_id);
					$members = $this->grm->numberOfAssignedUsers([$orgu->getSuperiorRole(),$orgu->getEmployeeRole()]);
					$member_counter->addNode($sub_ref_id, $members, $ref_id);
				}
			}
		}

		$rec_member_count = $member_counter->recursiveMembers();
		$this->remove(array_keys(array_filter($rec_member_count, function ($cnt) {
			return $cnt === 0;
		})));
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
