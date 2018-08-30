<?php

class ilTMSOrgUnitUserAssignmentQueriesWrapper {
	const POOL_SIZE = 100;
	const ASSIGNMENTS = "assignments";
	const RECURSIVE = "recursove";
	const NONE_RECURSIVE = "none_recursive";
	const USER_IDS = "user_ids";
	const WITH_AUTHORITY = "with_authority";
	const GLOBAL_NODE = "global_node";

	public function __construct(ilOrgUnitUserAssignmentQueries $ilias_queries, ilGlobalCache $global_cache = null) {
		$this->ilias_queries = $ilias_queries;
		$this->global_cache = $global_cache;
	}

	/**
	 * @param $user_id
	 *
	 * @return ilOrgUnitPosition[]
	 */
	public function getPositionsOfUserId($user_id) {
		return $this->ilias_queries->getPositionsOfUserId($user_id);
	}


	/**
	 * @param int $user_id
	 * @param int $position_id
	 * @param int $orgu_id Org-Units Ref-ID
	 *
	 * @return \ActiveRecord
	 * @throws \ilException
	 */
	public function getAssignmentOrFail($user_id, $position_id, $orgu_id) {
		return $this->ilias_queries->getAssignmentOrFail($user_id, $position_id, $orgu_id);
	}


	public function filterUserIdsDueToAuthorities($user_id, array $user_ids) {
		return $this->ilias_queries->filterUserIdsDueToAuthorities($user_id, $user_ids);
	}


	/**
	 * @param $user_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getAssignmentsOfUserId($user_id) {
		$node = $this->getNodePool($user_id);
		$data = $this->getDataForNode($node);
		if(isset($user_id, $data[$user_id][self::ASSIGNMENTS])) {
			return $data[$user_id][self::ASSIGNMENTS];
		}
		$assignments = $this->ilias_queries->getAssignmentsOfUserId($user_id);
		$data[$user_id][self::ASSIGNMENTS] = $assignments;
		$this->setDataToCache($node, $data);
		return $assignments;
	}


	/**
	 * @param $orgunit_ref_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserIdsOfOrgUnit($orgunit_ref_id) {
		return $this->ilias_queries->getUserIdsOfOrgUnit($orgunit_ref_id);
	}


	/**
	 * @param $orgunit_ref_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserIdsOfOrgUnits(array $orgunit_ref_id) {
		return $this->ilias_queries->getUserIdsOfOrgUnits($orgunit_ref_id);
	}


	/**
	 * @param      $position_id
	 * @param      $user_id
	 *
	 * @param bool $recursive
	 *
	 * @return \ilOrgUnitUserAssignment[]
	 * @internal param $orgunit_ref_id
	 */
	public function getUserIdsOfOrgUnitsOfUsersPosition($position_id, $user_id, $recursive = false) {
		$node = $this->getNodePool($user_id);
		$data = $this->getDataForNode($node);
		$recu = self::NONE_RECURSIVE;
		if($recursive) {
			$recu = self::RECURSIVE;
		}
		if(isset($user_id, $data[$user_id][self::USER_IDS][$position_id][$recu])) {
			return $data[$user_id][self::USER_IDS][$position_id][$recu];
		}
		$user_ids = $this->ilias_queries->getUserIdsOfOrgUnitsOfUsersPosition($position_id, $user_id, $recursive);
		$data[$user_id][self::USER_IDS][$position_id][$recu] = $user_ids;
		$this->setDataToCache($node, $data);
		return $user_ids;
	}


	/**
	 * @param array $orgu_ids
	 * @param       $position_id
	 *
	 * @return int[]
	 */
	public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, $position_id) {
		return $this->ilias_queries->getUserIdsOfOrgUnitsInPosition($orgu_ids, $position_id);
	}


	/**
	 * @param       $user_id
	 * @param       $users_position_id
	 * @param       $position_id
	 *
	 * @param bool  $recursive
	 *
	 * @return int[]
	 */
	public function getUserIdsOfUsersOrgUnitsInPosition($user_id, $users_position_id, $position_id, $recursive = false) {
		$node = $this->getNodePool($user_id);
		$data = $this->getDataForNode($node);
		$recu = self::NONE_RECURSIVE;
		if($recursive) {
			$recu = self::RECURSIVE;
		}
		if(isset($user_id, $data[$user_id][self::USER_IDS][$position_id][$users_position_id][$recu])) {
			return $data[$user_id][self::USER_IDS][$position_id][$users_position_id][$recu];
		}
		$user_ids = $this->ilias_queries->getUserIdsOfUsersOrgUnitsInPosition($user_id, $users_position_id, $position_id, $recursive);
		$data[$user_id][self::USER_IDS][$position_id][$users_position_id][$recu] =  $user_ids;
		$this->setDataToCache($node, $data);
		return $user_ids;
	}


	/**
	 * @param      $position_id
	 * @param      $user_id
	 *
	 * @param bool $recursive
	 *
	 * @return int[]
	 */
	public function getOrgUnitIdsOfUsersPosition($position_id, $user_id, $recursive = false) {
		return $this->ilias_queries->getOrgUnitIdsOfUsersPosition($position_id, $user_id, $recursive);
	}


	/**
	 * @param $position_id
	 *
	 * @return int[]
	 */
	public function getUserIdsOfPosition($position_id) {
		return $this->ilias_queries->getUserIdsOfPosition($position_id);
	}


	/**
	 * @param $position_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserAssignmentsOfPosition($position_id) {
		return $this->ilias_queries->getUserAssignmentsOfPosition($position_id);
	}

	/**
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function deleteAllAssignmentsOfUser($user_id) {
		return $this->ilias_queries->deleteAllAssignmentsOfUser($user_id);
	}

	/**
	 * Get user with any position
	 *
	 * @return int[] 	$user_ids
	 */
	public function getUserIdsWithAtLeastOnePosition() {
		$data = $this->getDataForNode(self::GLOBAL_NODE);
		if(isset($user_id, $data[self::WITH_AUTHORITY])) {
			return $data[self::WITH_AUTHORITY];
		}
		$user_ids = $this->ilias_queries->getUserIdsWithAtLeastOnePosition();
		$data[self::WITH_AUTHORITY] = $user_ids;
		$this->setDataToCache($node, $data);
		return $user_ids;
	}

	protected function getNodePool($user_id) {
		return floor($user_id/self::POOL_SIZE);
	}

	protected function getDataForNode($node) {
		$data = $this->global_cache->get((string)$node);
		if(is_null($data)) {
			$data = array();
		} 
		return $data;
	}

	protected function setDataToCache($node, $data) {
		 $this->global_cache->set((string)$node, $data);
	}
}