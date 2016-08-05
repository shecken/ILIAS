<?php
interface Actions {
	/**
	 * get ilObjUser
	 *
	 * @param 	int 	$user_id
	 *
	 * @return ilObjUser
	 */
	public function getUserObj($user_id);

	/**
	 * get all user ids with exit date
	 *
	 * @return int[]
	 */
	public function getExitedUserIds();

	/**
	 * get crs ids where $user_is booked to
	 *
	 * @param 	int 	$user_id
	 *
	 * @return int[]
	 */
	public function getBookedCoursesFor($user_id);

	/**
	 * set user tobe released in WBD
	 *
	 * @param 	int 	$user_id
	 */
	public function setUserToWBDRelease($user_id);

	/**
	 *
	 * @param 	int 	$crs_id
	 *
	 * @return ilDate;
	 */
	public function getStartDateOf($crs_id);

	/**
	 * user canceld on course
	 *
	 * @param 	int 	$crs_id
	 * @param 	int 	$user_id
	 */
	public function cancelBookings($crs_id, $user_id);

	/**
	 * mail of $topic is send to user
	 *
	 * @param 	string 	$topic
	 * @param 	int 	$crs_id
	 * @param 	int 	$user_id
	 */
	public function sendMail($topic, $crs_id, $user_id);

	/**
	 * assignments to all orgunts are removed
	 *
	 * @param 	int 	$user_id
	 */
	public function deassignOrgUnits($user_id);

	/**
	 * user assigned to Orgunit for exited user
	 *
	 * @param 	int 	$user_id
	 */
	public function assignUserToExitOrgu($user_id);

	/**
	 * 
	 * @param 	int 	$user_id
	 */
	public function getUserNAsOf($user_id);

	/**
	 * 
	 * @param 	int 	$na
	 */
	public function moveNAToNoAdviserOrgUnit($na);

	/**
	 * delete personal NA orgunit of $user
	 *
	 * @param 	int 	$user_id
	 */
	public function removeNAOrgUnitOf($user_id);

	/**
	 * delete alle empty orgunits below NA base
	 */
	public function purgeEmptyNABaseChildren();
}