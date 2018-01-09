<?php

namespace CaT\IliasUserOrguImport;

use CaT\UserOrguImport\User\Users as Users;
/**
 * Utility-class to handle exited user Logic.
 */
class ExitUserManagement
{

	public function __construct()
	{

	}

	public function exitUsers()
	{
		$diff = $this->getDifference(
					$this->getUsersInExitOrgus(),
					$this->getUsersDueToExit());
		foreach($diff->toAdd() as $usr) {
			if($exit_orgu_users->contains($usr)) {
				$this->exitUser($usr);
			}
		}

		foreach($diff->toRemove() as $usr) {
			if(!$this->userDueToExit($usr)) {
				$this->removeUserFromExitOrgus($usr);
			}
		}
	}

	/**
	 * Exit a user: deassign any role, except User, move to appropriate Exit-Orgu,
	 * set user inactive, remove from any other orgu within the import tree besides 
	 * exit tree.
	 *
	 * @param	int	$usr_id
	 * @return	void
	 */
	protected function exitUser(User\IliasUser $usr)
	{

	}

	/**
	 * Get any relevant usr due to exit.
	 *
	 * @return	Users
	 */
	protected function getUsersDueToExit()
	{
		return $this->u_loc->usersByUserIds($this->getUserIdsDueToExit());
	}

	/**
	 * Get the user ids of any relevant user due to exit.
	 *
	 * @return	int[]
	 */
	protected function getUserIdsDueToExit()
	{

	}

	/**
	 * Get any relevant user in any exit Orgu.
	 *
	 * @return	Users
	 */
	protected function getUsersInExitOrgus()
	{
		return $this->u_loc->usersByUserIds($this->getUserIdsInExitOrgus());
	}

	/**
	 * Get the User id of any relevant user within the exit subtree.
	 *
	 * @return	int[]
	 */
	public function getUserIdsInExitOrgus()
	{

	}

	/**
	 * Check whether the user is due to exit.
	 *
	 * @param	User\IliasUser	$usr
	 * @return	bool
	 */
	protected function userDueToExit(User\IliasUser $usr)
	{
		$exit_date = $usr->properties()[User\UdfWrapper::PROP_EXIT_DATE];
		if(trim((string)$exit_date) === '') {
			return false;
		}
		return $exit_date <= date('Y-m-d');
	}

	/**
	 * Remove the user from Exit Orgus. We assume, that the active status was established
	 * during User difference import and the proper assignments were performed at UserOrgu
	 * difference import.
	 *
	 * @param	User\IliasUser	$usr
	 * @return	void
	 */
	protected function removeUserFromExit(User\IliasUser $usr)
	{
		assert('is_int($usr_id)');
	}
}