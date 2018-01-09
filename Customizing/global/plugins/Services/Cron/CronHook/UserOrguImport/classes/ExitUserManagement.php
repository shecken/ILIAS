<?php

namespace CaT\IliasUserOrguImport;

use CaT\UserOrguImport\User\Users as Users;
use CaT\UserOrguImport as UOI;

/**
 * Utility-class to handle exited user Logic.
 */
class ExitUserManagement
{

	const ORGU_EXIT_IMPORT_ID_PREFIX = 'sap_import_exit_orgu_';

	protected $role_management;
	protected $orgu_config;
	protected $u_loc;
	protected $udf;
	protected $orgu_tree ;
	protected $log;
	protected $error_collection;

	public function __construct(
		IliasGlobalRoleManagement $role_management,
		Orgu\OrguCongig $orgu_config,
		User\UserLocator $u_loc,
		User\UdfWrapper $udf,
		\ilObjOrgUnitTree $orgu_tree,
		Log\Log $log,
		ErrorReporting\ErrorCollection $error_collection
	) {

		$this->role_management = $role_management;
		$this->orgu_config = $orgu_config;
		$this->u_loc = $u_loc;
		$this->udf = $udf;
		$this->orgu_tree = $orgu_tree;
		$this->log = $log;
		$this->error_collection = $error_collection;
	}

	/**
	 * Search for users due to exit and exit them or
	 * search for users not in exit anymore and remove them from exit.
	 *
	 * @return	void
	 */
	public function exitUsers()
	{
		$exit_diff = $this->getDifference(
			$this->getUsersInExitOrgus(),
			$this->getUsersDueToExit()
		);

		foreach ($diff->toCreate() as $usr) {
			try {
				$this->exitUser($usr);
			} catch (\Exception $e) {
				$this->error_collection->addError('fatal error during exit user '.$usr->properties()[User\UdfWrapper::PROP_PNR].
													':'.$e->getMessage());
			}
		}

		foreach ($diff->toDelete() as $usr) {
			try {
				$this->removeFromExit($usr);
			} catch (\Exception $e) {
				$this->error_collection->addError('fatal error during removing exit user '.$usr->properties()[User\UdfWrapper::PROP_PNR].
													':'.$e->getMessage());
			}
		}
	}

	/**
	 * @return	UOI\User\UsersDifference
	 */
	protected function getDifference(UOI\User\Users $left, UOI\User\Users $right)
	{
		return new UOI\User\UsersDifference($left, $right);
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
		$orgu = $this->getExitOrguByLETitle($this->getLEOfUsr($usr));
		$orgu->assignUsersToEmployeeRole([$usr_id]);

		foreach ($this->role_management->assignedRoles($usr) as $role_id) {
			$this->role_management->deassignFromUser($role_id, $usr);
		}
		$props = $usr->properties();
		$this->log->createEntry(
			'exit user:'.Base\Log\DatabaseLog::arrayToString($props),
			['pnr' => $props[UdfWrapper::PROP_PNR]]
		);
	}

	/**
	 * Extract the LE of a user from user properties.
	 *
	 * @param	User\IliasUser	$usr
	 * @return	string
	 */
	protected function getLEOfUsr(User\IliasUser $usr)
	{
		$orgu_path_string = $usr->properties()[User\UdfWrapper::PROP_ORGUS];
		return explode(', ', $orgu_path_string)[0];
	}

	/**
	 * Fetch exit orgu-object corresponding to the le-title.
	 * Create on the fly, if necessary.
	 *
	 * @param	string	$le_title
	 * @return	\ilObjOrgUnit
	 */
	protected function getExitOrguByLETitle($le_title)
	{
		assert('is_string($le_title)');
		if (strlen($le_title) === 0) {
			throw new \Exception('undefinite LE');
		}
		$import_id = self::ORGU_EXIT_IMPORT_ID_PREFIX.$le_title;
		$obj_id = (int)\ilObjOrgUnit::_lookupObjIdByImportId($import_id);
		if ($obj_id === 0) {
			return $this->createOrguWithImportId($import_id);
		} else {
			$orgu = new \ilObjOrgUnit(array_shift(\ilObjOrgUnit::_getAllReferences($obj_id)));
			$orgu->read();
			return $orgu;
		}
	}

	/**
	 * Create orgu with a given import id.
	 *
	 * @param	string	$import_id
	 * @return	\ilObjOrgUnit
	 */

	protected function createOrguWithImportId($import_id)
	{
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
	 * Remove the user from Exit Orgus. We assume that the active status was established
	 * during User difference import and the proper assignments were performed at UserOrgu
	 * difference import.
	 *
	 * @param	User\IliasUser	$usr
	 * @return	void
	 */
	protected function removeFromExit(User\IliasUser $usr)
	{
		$usr_id = $usr->iliasId();
		$exit_orugs = $this->orgu_tree->getOrgUnitOfUser($usr_id, $this->orgu_config->getExitRefId());
		foreach ($exit_orugs as $ref_id) {
			$orgu = new \ilObjOrgUnit($ref_id);
			$orgu->deassignUserFromEmployeeRole($usr_id);
		}
		$props = $usr->properties();
		$this->log->createEntry(
			'removing user from exit:'.Base\Log\DatabaseLog::arrayToString($props),
			['pnr' => $props[UdfWrapper::PROP_PNR]]
		);
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
		while ($rec = $this->db->fetchAssoc($res)) {
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
				.'		AND '.$this->db->in('rel_users.usr_id', $exited_usr_ids, false, 'integer');
		$res = $this->db->query($query);
		$return = [];
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[] = (int)$rec['usr_id'];
		}
		return $return;
	}
}
