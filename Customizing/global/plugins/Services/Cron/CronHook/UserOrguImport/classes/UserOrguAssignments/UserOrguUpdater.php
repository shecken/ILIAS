<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

use CaT\UserOrguImport\UserOrguAssignment as UOA;
use CaT\UserOrguImport\User as U;
use CaT\IliasUserOrguImport as Base;

/**
 * Ilias side user orgu assignment updates.
 */
class UserOrguUpdater
{

	public function __construct(
		UserOrguLocator $uol,
		UserOrguFunctionConfigDB $uofcd,
		Base\IliasGlobalRoleManagement $ugrm,
		Base\User\UdfWrapper $udf,
		Base\ErrorReporting\ErrorCollection $ec,
		Base\Log\Log $log
	) {


		$this->uol = $uol;
		$this->uofc = $uofcd->load();
		$this->ugrm = $ugrm;
		$this->ec = $ec;
		$this->ur_u = $ur_u;
		$this->udf = $udf;
		$this->rc = $rc;
		$this->log = $log;
	}

	/**
	 * Apply UOA\AssignmentsDifference to Ilias
	 *
	 * @param	UOA\AssignmentsDifference	$diff
	 * @return void
	 */
	public function applyDiff(UOA\AssignmentsDifference $diff)
	{
		$this->add($diff->toCreate());
		$this->remove($diff->toDelete());
	}


	/**
	 * Remove assignments with provided information.
	 *
	 * @param	UOA\Assignments	$ass_s
	 * @return	void
	 */
	public function remove(UOA\Assignments $ass_s)
	{
		$assignments = [];
		foreach ($ass_s as $ass) {
			$usr_id = $ass->iliasUserId();
			$org_ref_id = $ass->iliasOrguRefId();
			$ilias_role = $ass->iliasRole();
			$properties = $ass->properties();
			if (!isset($assignments[$org_ref_id])) {
				$assignments[$org_ref_id] = [];
			}
			if (!isset($assignments[$org_ref_id][$ilias_role])) {
				$assignments[$org_ref_id][$ilias_role] = [];
			}
			$assignments[$org_ref_id][$ilias_role][] = $ass;
		}
		$superior_global = $this->uofc->superiorGlobalRoleId();
		$employee_global = $this->uofc->employeeGlobalRoleId();

		foreach ($assignments as $ref_id => $roles) {
			$orgu = new \ilObjOrgUnit($ref_id);
			$orgu_title = $orgu->getTitle();
			foreach ($roles as $role => $usr_ass_s) {
				switch ($role) {
					case UserOrguAssignment::ILIAS_SUPERIOR:
						foreach ($usr_ass_s as $usr_ass) {
							$properties = $usr_ass->properties();
							$usr_id = $usr_ass->iliasUserId();
							$orgu->deassignUserFromSuperiorRole($usr_id);
							$this->ugrm->deassignByUsrId($superior_global, $usr_id);
							$this->log->createEntry(
								'deassigning user from superior',
								['pnr' => $properties[Base\User\UdfWrapper::PROP_PNR],
								'orgu_id' => $properties[Base\Orgu\OrguAMDWrapper::PROP_ID],
								'orgu_title' => $orgu_title]
							);
						}
						break;
					case UserOrguAssignment::ILIAS_EMPLOYEE:
						foreach ($usr_ass_s as $usr_ass) {
							$properties = $usr_ass->properties();
							$usr_id = $usr_ass->iliasUserId();
							$orgu->deassignUserFromEmployeeRole($usr_id);
							$this->ugrm->deassignByUsrId($employee_global, $usr_id);
							$this->log->createEntry(
								'deassigning user from employee',
								['pnr' => $properties[Base\User\UdfWrapper::PROP_PNR],
								'orgu_id' => $properties[Base\Orgu\OrguAMDWrapper::PROP_ID],
								'orgu_title' => $orgu_title]
							);
						}
						break;
					default:
						throw new \InvalidArgumentException('Unknown role '.$role);
				}
			}
		}
	}

	/**
	 * Add assignments with provided information.
	 *
	 * @param	UOA\Assignments	$ass_s
	 * @return	void
	 */
	public function add(UOA\Assignments $ass_s)
	{
		$orgus = $this->uol->getOrguRefIdByImportIds();
		$usr_ids_by_pnr = $this->uol->geUserIdsByPNR();
		$pnrs_by_usr_id = array_flip($usr_ids_by_pnr);
		$import_ids_by_orgu_ref = array_flip($orgus);

		$assignments = [];
		foreach ($ass_s as $ass) {
			$properties = $ass->properties();
			$usr_id = null;
			$org_ref_id = null;
			$ilias_role = null;

			if (isset($properties[Base\User\UdfWrapper::PROP_PNR])) {
				$usr_id = $usr_ids_by_pnr[$properties[Base\User\UdfWrapper::PROP_PNR]];
			}
			if ($usr_id === null) {
				$this->ec->addError('User assignment: could not locate user with PNR; '
					.$properties[Base\User\UdfWrapper::PROP_PNR]);
				continue;
			}
			$org_ref_id = $orgus[$properties[Base\Orgu\OrguAMDWrapper::PROP_ID]];
			if ($org_ref_id === null) {
				$this->ec->addError('User assignment: could not locate org-unit '.$properties[Base\Orgu\OrguAMDWrapper::PROP_ID]);
				continue;
			}
			if ($properties[UserOrguAMDWrapper::PROP_ROLE] === UserOrguIdentifier::ROLE_SUPERIOR) {
				$ilias_role = UserOrguAssignment::ILIAS_SUPERIOR;
			}
			if ($properties[UserOrguAMDWrapper::PROP_ROLE] === UserOrguIdentifier::ROLE_EMPLOYEE) {
				$ilias_role = UserOrguAssignment::ILIAS_EMPLOYEE;
			}
			if ($ilias_role === null) {
				$this->ec->addError('User assignment: indefinite role '.$properties[UserOrguAMDWrapper::PROP_ROLE]);
				continue;
			}
			if (!isset($assignments[$org_ref_id])) {
				$assignments[$org_ref_id] = [];
			}
			if (!isset($assignments[$org_ref_id][$ilias_role])) {
				$assignments[$org_ref_id][$ilias_role] = [];
			}
			$assignments[$org_ref_id][$ilias_role][] = $usr_id;
		}

		$superior_global = $this->uofc->superiorGlobalRoleId();
		$employee_global = $this->uofc->employeeGlobalRoleId();
		foreach ($assignments as $ref_id => $roles) {
			$orgu = new \ilObjOrgUnit($ref_id);
			$orgu_title = $orgu->getTitle();
			$import_id = $import_ids_by_orgu_ref[$ref_id];
			foreach ($roles as $role => $usr_ids) {
				switch ($role) {
					case UserOrguAssignment::ILIAS_SUPERIOR:
						$orgu->assignUsersToSuperiorRole($usr_ids);
						foreach ($usr_ids as $usr_id) {
							$a_usr_id = (int)$usr_id;
							$this->ugrm->assignByUsrId($superior_global, $a_usr_id);
							$this->log->createEntry(
								'assigning user to superior',
								['pnr' => $pnrs_by_usr_id[$usr_id],
								'orgu_id' => $import_id,
								'orgu_title' => $orgu_title]
							);
						}
						break;
					case UserOrguAssignment::ILIAS_EMPLOYEE:
						$orgu->assignUsersToEmployeeRole($usr_ids);
						foreach ($usr_ids as $usr_id) {
							$a_usr_id = (int)$usr_id;
							$this->ugrm->assignByUsrId($employee_global, $a_usr_id);
							$this->log->createEntry(
								'assigning user to employee',
								['pnr' => $pnrs_by_usr_id[$usr_id],
								'orgu_id' => $import_id,
								'orgu_title' => $orgu_title]
							);
						}
						break;
					default:
						throw new \InvalidArgumentException('Unknown role '.$role);
				}
			}
		}
	}
}
