<?php

namespace CaT\IliasUserOrguImport;

use CaT\UserOrguImport\User\Users as Users;
/**
 * Utility-class to handle exited user Logic.
 */
class ExitUserManagement
{

	const ORGU_EXIT_IMPORT_ID_PREFIX = 'sap_import_exit_orgu_';

	public function __construct()
	{

	}

	public function exitUsers()
	{
		$exit_diff = $this->getDifference(
						$this->getUsersInExitOrgus(),
						$this->getUsersDueToExit());

		foreach($diff->toCreate() as $usr) {
			try {
				$this->exitUser($usr);
			} catch(\Exception $e) {

			}
		}

		foreach($diff->toDelete() as $usr) {
			try {
				$this->removeFromExit($usr);
			} catch(\Exception $e) {

			}
		}
	}

	/**
	 * Exit a user: deassign any role, except User, move to appropriate Exit-Orgu.
	 * We assume that the deassignment from any other import orgus will take place
	 * during user orgu difference import.
	 *
	 * @param	int	$usr_id
	 * @return	void
	 */
	protected function exitUser(User\IliasUser $usr)
	{
		$usr_id = $usr->iliasId();
		$orgu = $this->getOrguByLETitle($this->getLEOfUsr($usr));
		$orgu->assignUsersToEmployeeRole([$usr_id]);

		foreach($this->role_management->assignedRoles($usr) as $role_id) {
			$this->role_management->deassignFromUser($role_id, $usr);
		}
	}

	protected function getLEOfUsr(User\IliasUser $usr)
	{
		$orgu_path_string = $usr->properties()[User\UdfWrapper::PROP_ORGUS];
		return explode(', ',$orgu_path_string)[0];
	}

	protected function getOrguByLETitle($le_title)
	{
		assert('is_string($le_title)');
		assert('strlen($le_title) > 0');
		$import_id = self::ORGU_EXIT_IMPORT_ID_PREFIX.$le_title;
		$obj_id = (int)\ilObjOrgUnit::_lookupObjIdByImportId($import_id);
		if($obj_id === 0) {
			return $this->createOrguWithImportId($import_id);
		} else {
			$orgu = new \ilObjOrgUnit(array_shift(\ilObjOrgUnit::_getAllReferences($obj_id)));
			$orgu->read();
			return $orgu;
		}
	}

	protected function createOrguWithImportId($import_id) {
		assert('is_string($import_id)');
		$orgu = new \ilObjOrgUnit();
		$orgu->create(true);
		$orgu->createReference();
		$orgu->putInTree($this->orgu_config->getExitRefId());
		$orgu->initDefaultRoles();
		$orgu->setImportId($import_id);
		$orgu->update();
		return $orgu;
	}

	/**
	 * Remove the user from Exit Orgus. We assume, that the active status was established
	 * during User difference import and the proper assignments were performed at UserOrgu
	 * difference import.
	 *
	 * @param	User\IliasUser	$usr
	 * @return	void
	 */
	protected function removeFromExit(User\IliasUser $usr)
	{
		$usr_id = $usr->iliasId();
		$exit_orugs = $this->orgu_tree->getOrgUnitOfUser($usr_id,$this->orgu_config->getExitRefId());
		foreach ($exit_orugs as $ref_id) {
			$orgu = new \ilObjOrgUnit($ref_id);
			$orgu->deassignUserFromEmployeeRole($usr_id);
		}
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
		$query =
				'SELECT rel_users.usr_id'
				.'	FROM udf_text rel_users'
				.'	JOIN udf_text exit_dates'
				.'		ON rel_users.usr_id = exit_dates.usr_id'
				.'	WHERE rel_users.field_id = '.$this->udf->fieldId(User\UdfWrapper::PROP_PNR)
				.'		AND exit_dates.field_id = '.$this->udf->fieldId(User\UdfWrapper::PROP_EXIT_DATE)
				.'		AND rel_users.value IS NOT NULL'	// non empty values for pnr
				.'		AND rel_users.value NOT REGEXP \'^[[:space:]]*$\''
				.'		AND exit_dates.value IS NOT NULL'	// properly formatted date-fields for exit date
				.'		AND exit_dates.value REGEXP \'^[[:space:]]*[0-9]{4}\\-[0-9]{2}\\-[0-9]{2}[[:space:]]*$\''
				.'		AND exit_dates.value <= CURDATE()';	//	exit date past
		$res = $this->db->query($query);
		$return = [];
		while($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['usr_id'];
		}
		return $return;
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
		$exited_usr_ids = $this->orgu_tree->getEmployees($this->orgu_config->getExitRefId(), true);

		$query =
				'SELECT usr_id'
				.'	FROM udf_text rel_users'
				.'	WHERE rel_users.field_id = '.$this->udf->fieldId(User\UdfWrapper::PROP_PNR)
				.'		AND rel_users.value IS NOT NULL'	// non empty values for pnr
				.'		AND rel_users.value NOT REGEXP \'^[[:space:]]*$\''
				.'		AND '.$this->db->in('rel_users.usr_id',$exited_usr_ids,false,'integer');
		$res = $this->db->query($query);
		$return = [];
		while($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['usr_id'];
		}
		return $return;
	}
}