<?php 

interface TMSAssignmentQueries {
		/**
	 * @param $user_id
	 *
	 * @return ilOrgUnitPosition[]
	 */
	public function getPositionsOfUserId($user_id);


	/**
	 * @param int $user_id
	 * @param int $position_id
	 * @param int $orgu_id Org-Units Ref-ID
	 *
	 * @return \ActiveRecord
	 * @throws \ilException
	 */
	public function getAssignmentOrFail($user_id, $position_id, $orgu_id);


	public function filterUserIdsDueToAuthorities($user_id, array $user_ids);


	/**
	 * @param $user_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getAssignmentsOfUserId($user_id);


	/**
	 * @param $orgunit_ref_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserIdsOfOrgUnit($orgunit_ref_id);


	/**
	 * @param $orgunit_ref_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserIdsOfOrgUnits(array $orgunit_ref_id);


	/**
	 * @param      $position_id
	 * @param      $user_id
	 *
	 * @param bool $recursive
	 *
	 * @return \ilOrgUnitUserAssignment[]
	 * @internal param $orgunit_ref_id
	 */
	public function getUserIdsOfOrgUnitsOfUsersPosition($position_id, $user_id, $recursive = false);


	/**
	 * @param array $orgu_ids
	 * @param       $position_id
	 *
	 * @return int[]
	 */
	public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, $position_id);


	/**
	 * @param       $user_id
	 * @param       $users_position_id
	 * @param       $position_id
	 *
	 * @param bool  $recursive
	 *
	 * @return int[]
	 */
	public function getUserIdsOfUsersOrgUnitsInPosition($user_id, $users_position_id, $position_id, $recursive = false);


	/**
	 * @param      $position_id
	 * @param      $user_id
	 *
	 * @param bool $recursive
	 *
	 * @return int[]
	 */
	public function getOrgUnitIdsOfUsersPosition($position_id, $user_id, $recursive = false);


	/**
	 * @param $position_id
	 *
	 * @return int[]
	 */
	public function getUserIdsOfPosition($position_id);


	/**
	 * @param $position_id
	 *
	 * @return ilOrgUnitUserAssignment[]
	 */
	public function getUserAssignmentsOfPosition($position_id);

	/**
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function deleteAllAssignmentsOfUser($user_id);

	/**
	 * Get user with any position
	 *
	 * @return int[] 	$user_ids
	 */
	public function getUserIdsWithAtLeastOnePosition();
}