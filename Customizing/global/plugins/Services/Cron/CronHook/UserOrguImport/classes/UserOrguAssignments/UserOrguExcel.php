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
	const EXIT_DATE = 'exit_date';

	protected static $conversions_assignments = [
				'P.Nr.' => self::USER_ID,
				'LE' => Orgu\ExcelOrgus::COLUMN_LE,
				'Ressort' => Orgu\ExcelOrgus::COLUMN_RESSORT,
				'Abteilung' => Orgu\ExcelOrgus::COLUMN_DEPARTMENT,
				'Gruppe' => Orgu\ExcelOrgus::COLUMN_GROUP,
				'Team' => Orgu\ExcelOrgus::COLUMN_TEAM,
				'Anstellungsverhältn.' => self::COLUMN_FUNCTION,
				'Austrittsdatum' => self::EXIT_DATE
				];

	public function assignments()
	{
		$date = date('Y-m-d');
		$ass_s = new UserOrgu\Assignments($this->ident);
		$path = $this->import_files->getCurrentUserOrguFilePath();
		if (!$path) {
			$this->e_c->addError('User data file not accessible');
			return null;
		}
		foreach ($this->xlsx->extractContent($path, self::$conversions_assignments) as $row) {
			$row = $this->postprocessRow($row);
			if ($this->checkRow($row)) {
				if ($row[self::EXIT_DATE] === '' || $row[self::EXIT_DATE] > $date) { // skip assignments of exited users
					$ass_s->add(new UserOrgu\Assignment($row, $this->ident));
				}
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
		switch ($this->uofc->roleForFunction($row[self::COLUMN_FUNCTION])) {
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
		$return[self::EXIT_DATE] = $this->tryFormatDate($row[self::EXIT_DATE]);
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
		if ($row[self::EXIT_DATE] === false) {
			$this->ec->addError('undefined exit date for assignment '.Base\Log\DatabaseLog::arrayToString($row));
			return false;
		}
		return true;
	}

	protected function tryFormatDate($date_string)
	{
		if ($date_string === '' || $date_string === '#') {
			return '';
		}
		try {
			$date_time = new \DateTime($date_string);
			return $date_time->format('Y-m-d');
		} catch (\Exception $e) {
			return false;
		}
	}
}
