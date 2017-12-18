<?php

namespace CaT\IliasUserOrguImport\UserOrguAssignments;

use CaT\IliasUserOrguImport\Orgu as Orgu;
use CaT\IliasUserOrguImport\User as User;
use CaT\UserOrguImport\UserOrguAssignment as UserOrgu;
use CaT\UserOrguImport\User as IUser;
use CaT\IliasUserOrguImport\Filesystem\ImportFiles as ImportFiles;
use CaT\IliasUserOrguImport\Data\SpoutXLSXExtractor as XLSXExtractor;
use CaT\IliasUserOrguImport\ErrorReporting\ErrorCollection as ErrorCollection;
use CaT\IliasUserOrguImport as Base;

/**
 * Read the xlsx files and filter valid assignments
 */
class UserOrguExcel
{

	public function __construct(
		UserOrguLocator $uol,
		UserOrguFunctionConfig $uofc,
		ImportFiles $import_files,
		XLSXExtractor $xlsx,
		UserOrguIdentifier $ident,
		Base\User\UserIdentifier $u_ident,
		ErrorCollection $ec
	) {

		$this->uol = $uol;
		$this->uofc = $uofc;
		$this->import_files = $import_files;
		$this->xlsx = $xlsx;
		$this->ident = $ident;
		$this->u_ident = $u_ident;
		$this->ec = $ec;

		$this->pnrs = $this->uol->geUserIdsByPNR();

		$this->orgu_import_ids = $this->uol->getOrguRefIdByImportIds();
	}

	const USER_ID = 'user_id';
	const COMMULATIVE_ROLE = 'commulative_role';
	const COLUMN_FUNCTION = 'function';

	protected static $conversions_assignments = [
				'P.Nr.' => self::USER_ID,
				'LE' => Orgu\ExcelOrgus::COLUMN_LE,
				'Ressort' => Orgu\ExcelOrgus::COLUMN_RESSORT,
				'Abteilung' => Orgu\ExcelOrgus::COLUMN_DEPARTMENT,
				'Gruppe' => Orgu\ExcelOrgus::COLUMN_GROUP,
				'Team' => Orgu\ExcelOrgus::COLUMN_TEAM,
				'Anstellungsverhältn.' => self::COLUMN_FUNCTION
				];

	protected static $conversions_commulative = [
				'P.Nr' => self::USER_ID,
				'BuKr.' => self::COMMULATIVE_ROLE
				];


	public function assignments()
	{
		$ass_s = new UserOrgu\Assignments($this->ident);
		foreach ($this->xlsx->extractContent($this->import_files->getCurrentUserOrguFilePath(), self::$conversions_assignments) as $row) {
			$row = $this->postprocessRow($row);
			if ($this->checkRow($row)) {
				$ass_s->add(new UserOrgu\Assignment($row, $this->ident));
			} else {
				$this->ec->addError('row '.Base\Log\DatabaseLog::arrayToString($row).' invalid');
			}
		}
		return $ass_s;
	}

	protected function postprocessRow(array $row)
	{
		$return = [];

		$return[User\UdfWrapper::PROP_PNR] = (string)$row[self::USER_ID];
		switch($this->uofc->roleForFunction($row[self::COLUMN_FUNCTION])) {
			case UserOrguFunctionConfig::SUPERIOR_ROLE:
				$return[UserOrguAMDWrapper::PROP_ROLE] = UserOrguIdentifier::ROLE_SUPERIOR;
				break;
			case UserOrguFunctionConfig::EMPLOYEE_ROLE:
				$return[UserOrguAMDWrapper::PROP_ROLE] = UserOrguIdentifier::ROLE_EMPLOYEE;
				break;
			default:
				$return[UserOrguAMDWrapper::PROP_ROLE] = UserOrguIdentifier::ROLE_EMPLOYEE;
		}
		$return[Orgu\OrguAMDWrapper::PROP_ID] = Orgu\ExcelOrgus::idByOrguPath(Orgu\ExcelOrgus::normalizedOrguPath($row));

		return $return;
	}

	protected function checkRow(array $row)
	{
		if (trim((string)$row[User\UdfWrapper::PROP_PNR]) === '') {
			$this->ec->addError('no user id field set for assignment '.Base\Log\DatabaseLog::arrayToString($row));
			return false;
		}
		if (!isset($this->pnrs[$row[User\UdfWrapper::PROP_PNR]])) {
			$this->ec->addError('User '.$row[self::USER_ID].' seems not to exist in ilias');
			return false;
		}
		if (trim((string)$row[Orgu\OrguAMDWrapper::PROP_ID]) === '') {
			$this->ec->addError('no orgu id set for assignment '.Base\Log\DatabaseLog::arrayToString($row));
			return false;
		}
		if (!isset($this->orgu_import_ids[$row[Orgu\OrguAMDWrapper::PROP_ID]])) {
			$this->ec->addError('Orgu '.$row[Orgu\OrguAMDWrapper::PROP_ID].' seems not to exist in ilias');
			return false;
		}
		if ($row[UserOrguAMDWrapper::PROP_ROLE] !== UserOrguIdentifier::ROLE_EMPLOYEE
			&& $row[UserOrguAMDWrapper::PROP_ROLE] !== UserOrguIdentifier::ROLE_SUPERIOR ) {
			$this->ec->addError('undefined level for assignment '.Base\Log\DatabaseLog::arrayToString($row));
			return false;
		}
		return true;
	}

	/**
	 * Get an array of assignment data for extern roles.
	 * Delivered data will be checked and lacks faulty
	 * entries from file.
	 *
	 * @return	mixed[][]
	 */
	public function commulativeRoles()
	{
		$user_data = [];

		$pnr_to_user_id = $this->uol->geUserIdsByPNR();

		$file_path = $this->import_files->getCurrentUserOrguFilePath();

		if ($file_path) {
			foreach ($this->xlsx->extractContent($file_path, self::$conversions_commulative) as $row) {
				$row = $this->postprocessRow($row);
			}
		}

		$usrs = new IUser\Users($this->u_ident);
		foreach ($user_data as $il_user_id => $properties) {
			$usrs->add(new User\IliasUser($properties, $this->u_ident, $pnr_to_user_id[$row[self::USER_ID]]));
		}
		return $usrs;
	}
}
