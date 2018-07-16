<?php

/**
 * Helper to get user ids via positions and auhtorites
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class TMSPositionHelper {
	/**
	 * @var ilOrgUnitUserAssignmentQueries
	 */
	protected $orgua_queries;

	public function __construct(ilOrgUnitUserAssignmentQueries $orgua_queries) {
		$this->orgua_queries = $orgua_queries;
	}

	/**
	 * Get all user ids where user has authorities
	 *
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	public function getUserIdWhereUserHasAuhtority($user_id) {
		$positons = $this->getPositionsOf($user_id);

		$user_ids = array();
		foreach ($positons as $positon) {
			$result = array_map(
				function($u) { return (int)$u;},
				$this->getUserIdsByPositionAndUser($positon, $user_id)
			);

			$user_ids = array_merge($user_ids, $result);
		}

		return array_unique($user_ids);
	}

	/**
	 * Get all user id where position has authority for specified org units
	 *
	 * @param ilOrgUnitPosition[] 	$positions
	 * @param int[] 	$orgus
	 *
	 * @return int[]
	 */
	public function getUserIdsForPositionsAndOrgunits(array $positions, array $orgus) {
		$user_ids = array();
		foreach($positions as $position) {
			foreach($position->getAuthorities() as $authority) {
				$result = array_map(
					function($u) { return (int)$u;},
					$this->orgua_queries->getUserIdsOfOrgUnitsInPosition($orgus, $authority->getOver())
				);

				$user_ids = array_merge(
					$user_ids,
					$result
				);
			}
		}

		return array_unique($user_ids);
	}

	/**
	 * Get all orgu ids where use has any authority
	 *
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	public function getOrgUnitIdsWhereUserHasAuthority($user_id) {
		$positions = $this->getPositionsOfUserWithAuthority($user_id);
		return $this->getOrgUnitByPositions($positions, $user_id);
	}

	/**
	 * Get all org units where user as position
	 *
	 * @param ilOrgUnitPosition[] 	$positions
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	public function getOrgUnitByPositions(array $positions, $user_id) {
		$orgus = array();
		foreach($positions as $position) {
			$orgus = array_merge(
				$orgus,
				$orgus = $this->orgua_queries->getOrgUnitIdsOfUsersPosition($position->getId(), $user_id)
			);
		}

		return array_unique($orgus);
	}

	/**
	 * Get positions where user has any authority
	 *
	 * @param int 	$user_id
	 *
	 * @return ilOrgUnitPosition[]
	 */
	public function getPositionsOfUserWithAuthority($user_id) {
		$positions = $this->getPositionsOf($user_id);
		$positions = array_filter($positions, function($p) {
			if(count($p->getAuthorities()) > 0) {
				return $p;
			}
		});
		return $positions;
	}

	/**
	 * Get all orgu assignments of user
	 *
	 * @param int 	$user_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	protected function getAssignmentsOf($user_id) {
		return $this->orgua_queries->getAssignmentsOfUserId($user_id);
	}

	/**
	 * Get positions of user on his aussignments
	 *
	 * @param int 	$user_id
	 *
	 * @return ilOrgUnitPosition[]
	 */
	protected function getPositionsOf($user_id) {
		require_once("Modules/OrgUnit/classes/Positions/class.ilOrgUnitPosition.php");
		$assignments = $this->getAssignmentsOf($user_id);
		return array_map(function($a) {
			return new ilOrgUnitPosition($a->getPositionId());
		}, $assignments);
	}

	/**
	 * Get all user id via positions
	 *
	 * @param ilOrgUnitPosition 	$position
	 * @param int 	$user_id
	 *
	 * @return int[]
	 */
	protected function getUserIdsByPositionAndUser(ilOrgUnitPosition $position, $user_id) {
		require_once("Modules/OrgUnit/classes/Positions/Authorities/class.ilOrgUnitAuthority.php");
		$ids = array();
		foreach ($position->getAuthorities() as $authority) {
			switch ($authority->getOver()) {
				case ilOrgUnitAuthority::OVER_EVERYONE:
					switch ($authority->getScope()) {
						case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id)
							);
							break;
						case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id, true)
							);
							break;
					}
					break;
				default:
					switch ($authority->getScope()) {
						case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver())
							);
							break;
						case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
							$ids = array_merge(
								$ids,
								$this->orgua_queries->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver(), true)
							);
							break;
					}
			}
		}

		return $ids;
	}

	/**
	 * Get all user ids with at last one position
	 *
	 * @return int[]
	 */
	public function getUserIdsWithAtLeastOnePositionWithAuthority() {
		$assignemnts = $this->orgua_queries->getUserIdsWithAtLeastOnePosition();
		return array_keys($assignemnts);
	}

	/**
	 * Get user ids the given user has authority over
	 *
	 * @param int 	$usr_is
	 * @param \ilObjOrgUnitTree 	$orgu_tree
	 * @return int[]
	 */
	public function getAllVisibleUserIdsForUser(int $usr_id, \ilObjOrgUnitTree $orgu_tree) {
		$visible_users = [];
		$positions = $this->getPositionsOf($usr_id);
		foreach ($positions as $pkey => $position) {
			$orgus = $this->getOrgUnitByPositions([$position], $usr_id);
			$authorities = $position->getAuthorities();

			foreach ($authorities as $akey => $authority) {

				if((int)$authority->getScope() === \ilOrgUnitAuthority::SCOPE_SAME_ORGU) {
					if((int)$authority->getOver() === -1) {
						$v = $this->orgua_queries->getUserIdsOfOrgUnits($orgus);
					} else {
						$v = $this->orgua_queries->getUserIdsOfOrgUnitsInPosition($orgus, $authority->getOver());
					}
					$visible_users = array_merge($visible_users, $v);

				}
				if((int)$authority->getScope() === \ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS) {
					$subsequent_orgus = [];
					foreach ($orgus as $orgu_ref_id) {
						$lower_orgus = array_filter($orgu_tree->getAllChildren($orgu_ref_id),
							function($o) use ($orgu_ref_id){
								return $o !== $orgu_ref_id;
							}
						);
						$subsequent_orgus = array_merge($subsequent_orgus, $lower_orgus);
					}
					if((int)$authority->getOver() === -1) {
						$v = $this->orgua_queries->getUserIdsOfOrgUnits($subsequent_orgus);
					} else {
						$v = $this->orgua_queries->getUserIdsOfOrgUnitsInPosition($subsequent_orgus, $authority->getOver());
					}
					$visible_users = array_merge($visible_users, $v);
				}
			}


		}

		$visible_users  = array_map(
			function ($intlike_val) {
				return (int)$intlike_val;
			},
			array_unique($visible_users)
		);

		$visible_users = array_filter($visible_users,
			function($entry) use ($usr_id) {
				return $entry !== $usr_id;
			}
		);
		return $visible_users;
	}

}