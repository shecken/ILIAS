<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

use CaT\IliasUserOrguImport\User as User;
use CaT\IliasUserOrguImport\Orgu as Orgu;
use CaT\UserOrguImport\UserOrguAssignment as UOA;

/**
 * Locates relevant user orgu assignments and provides their data.
 */
class UserOrguLocator
{
	public function __construct(
		$db,
		$tree,
		$orgu_tree,
		UserOrguIdentifier $identifier,
		User\UdfWrapper $udf,
		Orgu\OrguConfig $o_cfg,
		User\UserLocator $u_loc
	) {

		$this->db = $db;
		$this->tree = $tree;
		$this->orgu_tree = $orgu_tree;
		$this->identifier = $identifier;
		$this->udf = $udf;
		$this->o_cfg = $o_cfg;
		$this->u_loc = $u_loc;
	}


	/**
	 * Get assignment objects corresponding to relevant user assignments in the
	 * import subtree.
	 *
	 * @return	UOA\Assignments
	 */
	public function getAssignmentsAmong(array $pnrs)
	{
		return $this->getAssignmentsBy(
			$this->getPNRsByUserIdAmong($pnrs),
			$this->getImportIdByRef(),
			$this->identifier,
			User\UdfWrapper::PROP_PNR
		);
	}

	/**
	 * Get assignemnts by provided data.
	 *
	 * @param	string[int]	$user_id_rels	pnr => usr_id
	 * @param	string[int]	$references	ref_id => import_id
	 * @param	UserOrguIdentifier	$ident
	 * @param 	string	$udf_key
	 */
	protected function getAssignmentsBy(
		array $user_id_rels,
		array $references,
		UserOrguIdentifier $ident,
		$udf_key
	) {

		$assignments = new UOA\Assignments($ident);
		foreach ($references as $ref_id => $import_id) {
			foreach ($this->orgu_tree->getEmployees($ref_id) as $user_id) {
				if (isset($user_id_rels[(int)$user_id])) {
					$assignments->add(
						new UserOrguAssignment(
							[
								$udf_key => $user_id_rels[(int)$user_id],
								Orgu\OrguAMDWrapper::PROP_ID => $import_id,
								UserOrguAMDWrapper::PROP_ROLE => UserOrguIdentifier::ROLE_EMPLOYEE
							],
							$ident,
							(int)$user_id,
							$ref_id,
							UserOrguAssignment::ILIAS_EMPLOYEE
						)
					);
				}
			}
			foreach ($this->orgu_tree->getSuperiors($ref_id) as $user_id) {
				if (isset($user_id_rels[(int)$user_id])) {
					$assignments->add(
						new UserOrguAssignment(
							[
								$udf_key => $user_id_rels[(int)$user_id],
								Orgu\OrguAMDWrapper::PROP_ID => $import_id,
								UserOrguAMDWrapper::PROP_ROLE => UserOrguIdentifier::ROLE_SUPERIOR
							],
							$ident,
							(int)$user_id,
							$ref_id,
							UserOrguAssignment::ILIAS_SUPERIOR
						)
					);
				}
			}
		}
		return $assignments;
	}


	/**
	 * All import_id => ref-id relations in the LOGA subtree
	 *
	 * @return	int[string]
	 */
	public function getImportIdByRef()
	{
		return $this->getImportByRefIdUnder($this->o_cfg->getRootRefId());
	}

	/**
	 * All import_id => ref-id relations within the relevant Orgus
	 *
	 * @return	int[string]
	 */
	public function getOrguRefIdByImportIds()
	{
		$return = [];
		foreach ($this->getImportIdByRef() as $ref_id => $import_id) {
			$return[$import_id] = $ref_id;
		}
		return $return;
	}

	/**
	 * Get ref_id => import_id relations in a subtree ref_id
	 *
	 * @param	int	$ref_id
	 * @return	string[int]
	 */
	protected function getImportByRefIdUnder($ref_id)
	{
		$relevant_orgus = array_diff(
			$this->tree->getSubTree($this->tree->getNodeTreeData($ref_id), false, 'orgu'),
			$this->tree->getSubTree($this->tree->getNodeTreeData($this->o_cfg->getExitRefId()), false, 'orgu')
		);
		$query =
			'SELECT ref_id, import_id'
			.'	FROM object_data'
			.'	JOIN object_reference USING(obj_id)'
			.'	WHERE '.$this->db->in(
				'ref_id',
				$relevant_orgus,
				false,
				'integer'
			).'	AND import_id IS NOT NULL AND import_id != \'\'';
		$res = $this->db->query($query);
		$return = [];
		while ($rec = $this->db->fetchAssoc($res)) {
			$return[(int)$rec['ref_id']] = $rec['import_id'];
		}
		return $return;
	}

	/**
	 * Get all usr_id - pnr relations
	 *
	 * @return int[string]
	 */
	public function getPNRsByUserIdAmong(array $relevant_pnrs)
	{
		return $this->udf->userIdsFieldRelation($this->udf->fieldId(User\UdfWrapper::PROP_PNR), $relevant_pnrs);
	}


	/**
	 * Get all pnr - usr_id relations
	 *
	 * @return string[int]
	 */
	public function geUserIdsByPNR()
	{
		return array_flip($this->getPNRsByUserId());
	}

	public function getPNRsByUserId()
	{
		return $this->udf->userIdsFieldRelation($this->udf->fieldId(User\UdfWrapper::PROP_PNR));
	}

	/**
	 * Get IliasUser corresponding to a usr_id
	 *
	 * @param	int	$usr_Id
	 * @return	IliasUser
	 */
	public function getUserByUserId($usr_id)
	{
		assert('is_int($usr_id)');
		return $this->u_loc->userByUserId($usr_id);
	}
}
