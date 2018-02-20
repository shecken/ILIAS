<?php

namespace CaT\IliasUserOrguImport\Orgu;



use CaT\UserOrguImport\Orgu as Orgu;
use CaT\IliasUserOrguImport as Base;

/**
 * Performs ilias-side orgu updates.
 */
class OrguUpdater
{

	public function __construct(
		OrguLocator $orgu_locator,
		OrguAMDWrapper $amd_wrapper,
		OrguConfig $cfg,
		$orgu_tree,
		$tree,
		$rep_utils,
		Base\IliasGlobalRoleManagement $irm,
		Base\ErrorReporting\ErrorCollection $ec,
		Base\Log\Log $log
	) {

		$this->orgu_locator = $orgu_locator;
		$this->amd_wrapper = $amd_wrapper;
		$this->cfg = $cfg;
		$this->orgu_tree = $orgu_tree;
		$this->tree = $tree;
		$this->rep_utils = $rep_utils;
		$this->irm = $irm;
		$this->ec = $ec;
		$this->log = $log;
	}

	protected static $desired_operations_superiors = [
			 "view_learning_progress"
			,"view_learning_progress_rec"
			,"read"
			,"visible"
			,"view_employee_bookings"
			,"view_employee_bookings_rcrsv"
			,"book_employees"
			,"book_employees_rcrsv"
			,"cancel_employee_bookings"
			,"cancel_employee_bookings_rcrsv"];

	protected static $desired_operations_employees = [];

	/**
	 * Apply OrgusDiff to Ilias
	 *
	 * @param	Orgu\OrgusDifference	$diff
	 * @return	void
	 */
	public function applyDiff(Orgu\AdjacentOrgUnitsDifference $diff)
	{
		$this->add($diff->toCreate());
		$this->update($diff->toChange());
		$this->remove($diff->toDelete());
	}

	/**
	 * Update orgus by provided information.
	 *
	 * @param	Orgu\AdjacentOrgUnits	$orgus
	 * @return	void
	 */
	public function update(Orgu\AdjacentOrgUnits $orgus)
	{
		foreach ($orgus as $orgu) {
			$this->updateOrgu($orgu);
		}
	}

	/**
	 * Update orgu by provided information.
	 *
	 * @param	IliasOrgu	$orgu
	 * @return	void
	 */
	public function updateOrgu(IliasOrgu $orgu)
	{

		$log_msg = [];
		$properties = $orgu->properties();
		$desired_parent_id = $orgu->parentOrguIdProperties()[OrguAMDWrapper::PROP_ID];
		$current_parent_id = $this->orgu_locator->orguById($properties[OrguAMDWrapper::PROP_ID])
								->parentOrguIdProperties()[OrguAMDWrapper::PROP_ID];
		if ($desired_parent_id !== $current_parent_id) {
			$log_msg[] = 'moving orgu from '.$current_parent_id.' to '.$desired_parent_id;
			$this->moveUnder($orgu, $this->orgu_locator->orguById($desired_parent_id));
		}
		$log_msg[] = 'update orgu properties to '.Base\Log\DatabaseLog::arrayToString($properties);
		$il_orgu = $this->updateOrguData(new \ilObjOrgUnit($orgu->refId()), $properties);
		$il_orgu->update();

		$this->log->createEntry(
			implode(',', $log_msg),
			['orgu_id' => $properties[OrguAMDWrapper::PROP_ID],
			'orgu_title' => $properties[OrguAMDWrapper::PROP_TITLE]]
		);
	}

	protected function updateOrguData(\ilObjOrgUnit $il_orgu, array $data)
	{
		$il_orgu->setTitle($data[OrguAMDWrapper::PROP_TITLE]);
		$il_orgu->setImportId($data[OrguAMDWrapper::PROP_ID]);
		return $il_orgu;
	}

	/**
	 * Add orgus with provided information.
	 *
	 * @param	Orgu\AdjacentOrgUnits	$orgus
	 * @return	void
	 */
	public function add(Orgu\AdjacentOrgUnits $orgus)
	{
		$to_add = [];
		global $ilLog;
		foreach ($orgus as $orgu) {
			$to_add[] = $orgu;
		}
		$cnt = 0;
		$max_count = count($to_add);
		while ($orgu = array_shift($to_add)) {
			$parent_id = $orgu->parentOrguIdProperties()[OrguAMDWrapper::PROP_ID];
			if ($parent_orgu = $this->orgu_locator->orguById($parent_id)) {
				$cnt = 0;
				$max_count = count($to_add);
				$il_orgu = $this->insertUnder($orgu, $parent_orgu);
				$il_orgu = $this->updateOrguData($il_orgu, $orgu->properties());
				$il_orgu->update();

				$orgu_ref_id = (int)$il_orgu->getRefId();
				$sup_role_id = (int)$il_orgu->getSuperiorRole();
				$emp_role_id = (int)$il_orgu->getEmployeeRole();

				$this->irm->setNewOperationsForRoleIdAtRefId($sup_role_id, $orgu_ref_id, self::$desired_operations_superiors);
				$this->irm->setNewOperationsForRoleIdAtRefId($emp_role_id, $orgu_ref_id, self::$desired_operations_employees);
				$properties = $orgu->properties();
				$this->log->createEntry(
					'inserting new orgu under '.$parent_id,
					['orgu_id' => $properties[OrguAMDWrapper::PROP_ID],
					'orgu_title' => $properties[OrguAMDWrapper::PROP_TITLE]]
				);
			} else {
				$cnt++;
				if ($cnt < $max_count) {
					array_push($to_add, $orgu);
				} else {
					$this->dealWithImpossibleAdds($orgu, $parent_id);
				}
			}
		}
	}

	protected function dealWithImpossibleAdds(Orgu\AdjacentOrgUnit $orgu, $parent_id)
	{
		assert('is_string($parent_id)');
		$props = $orgu->properties();
		$this->ec->addError('impossible to insert orgu with properties '.Base\Log\DatabaseLog::arrayToString($props)
							.' under '.$parent_id);
	}

	protected function insertUnder(Orgu\AdjacentOrgUnit $orgu, IliasOrgu $parent)
	{
		$parent_ref = $parent->refId();
		$il_orgu = new \ilObjOrgUnit();
		$il_orgu->create(true);
		$il_orgu->createReference();
		$il_orgu->putInTree($parent_ref);
		$il_orgu->initDefaultRoles();
		$il_orgu->update();
		return $il_orgu;
	}

	protected function moveUnder(IliasOrgu $orgu, IliasOrgu $designated_parent)
	{
		$ref_id = $orgu->refId();
		$designated_parent_id = $designated_parent->refId();
		$this->tree->moveTree($ref_id, $designated_parent_id);
	}

	/**
	 * Remove orgus with provided information.
	 *
	 * @param	Orgu\AdjacentOrgUnits	$orgus
	 * @return	void
	 */
	public function remove(Orgu\AdjacentOrgUnits $orgus)
	{
		$to_delete_rec = [];
		foreach ($orgus as $orgu) {
			assert('$orgu instanceof CaT\IliasUserOrguImport\Orgu\IliasOrgu');
			$ref_id = $orgu->refId();
			foreach ($this->tree->getSubTree($this->tree->getNodeTreeData($ref_id), false, 'orgu') as $c_ref_id) {
				if (!in_array((int)$c_ref_id, $to_delete_rec)) {
					$to_delete_rec[] = (int)$c_ref_id;
				}
			}
			$properties = $orgu->properties();
			$this->log->createEntry(
				'removing orgu: ',
				['orgu_id' => $properties[OrguAMDWrapper::PROP_ID],
				'orgu_title' => $properties[OrguAMDWrapper::PROP_TITLE]]
			);
		}
		if (count($to_delete_rec) > 0) {
			$to_delete = [];
			foreach ($to_delete_rec as $ref_id) {
				$parent_id = (int)$this->tree->getParentId($ref_id);
				if (!in_array($parent_id, $to_delete_rec)) {
					$to_delete[] = $ref_id;
				}
			}
			$this->rep_utils->deleteObjects($this->cfg->getRootRefId(), $to_delete);
		}
	}
}
