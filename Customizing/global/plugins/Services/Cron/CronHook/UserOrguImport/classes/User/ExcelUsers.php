<?php

#MUCH TO DO HERE

namespace CaT\IliasUserOrguImport\User;

use CaT\IliasUserOrguImport\Data as Data;
use CaT\IliasUserOrguImport\Filesystem\Filesystem as Filesystem;
use CaT\UserOrguImport\User as User;
use CaT\IliasUserOrguImport as Base;

/**
 * Extract user information from a xlsx file.
 */
class ExcelUsers
{

	const COLUMN_LE = 'le';
	const COLUMN_RESSORT = 'ressort';
	const COLUMN_DEPARTMENT = 'departement';
	const COLUMN_GROUP = 'group';
	const COLUMN_TEAM = 'team';


	protected $files;
	protected $extractor;


	public static $conversions = [
			'BuKr' => UdfWrapper::PROP_FLAG_KU,
			'LE' => self::COLUMN_LE,
			'Ressort' => self::COLUMN_RESSORT,
			'Abteilung' => self::COLUMN_DEPARTMENT,
			'Gruppe' => self::COLUMN_GROUP,
			'Team' => self::COLUMN_TEAM,
			'P.Nr.' => UdfWrapper::PROP_PNR,
			'NACHN' => UdfWrapper::PROP_LASTNAME,
			'VORNA' => UdfWrapper::PROP_FIRSTNAME,
			'Emailadresse MitArb' => UdfWrapper::PROP_EMAIL,
			'Kostenstelle' => UdfWrapper::PROP_COST_CENTRE,
			'Kostenstelle lang' => UdfWrapper::PROP_COST_CENTRE_LONG,
			'Anstellungsverhältn.' => UdfWrapper::PROP_FUNCTION,
			'Geschlecht' => UdfWrapper::PROP_GENDER,
			'Geburtsdatum' => UdfWrapper::PROP_BIRTHDAY ,
			'Eintrittsdatum in KU' => UdfWrapper::PROP_ENTRY_DATE_KU,
			'Eintrittsdatum in KO' => UdfWrapper::PROP_ENTRY_DATE_KO,
			'Inaktiv von' => UdfWrapper::PROP_INACTIVE_BEGIN,
			'Inaktiv bis' => UdfWrapper::PROP_INACTIVE_END,
			'Name Vorgesetzter' => UdfWrapper::PROP_SUPERIOR_OF_USR,
			'Austrittsdatum' => UdfWrapper::PROP_EXIT_DATE
			];

	public function __construct(
		Base\Filesystem\ImportFiles $import_files,
		Data\DataExtractor $extractor,
		UserIdentifier $ident,
		Base\ErrorReporting\ErrorCollection $e_c
	) {

		$this->import_files = $import_files;
		$this->extractor = $extractor;
		$this->ident = $ident;
		$this->e_c = $e_c;
	}


	/**
	 * Get User data from an excel file
	 *
	 * @return User\Users
	 */
	public function users()
	{
		$ident = $this->ident;
		$users = new User\Users($ident);
		$path = $this->import_files->getCurrentUserFilePath();
		if (!$path) {
			$this->e_c->addError('User data file not accessible');
			return null;
		}
		foreach ($this->extractor->extractContent($path, self::$conversions) as $row) {
			$row = $this->postprocessRow($row);
			if ($this->checkRow($row)) {
				$users->add(new User\User($row, $ident));
			} else {
				$this->e_c->addError('Invalid User data: '.Base\Log\DatabaseLog::arrayToString($row));
			}
		}
		return $users;
	}

	protected function checkRow(array $row)
	{
		$kz_ku_set = trim((string)$row[UdfWrapper::PROP_FLAG_KU]) !== '';
		$pnr_set = trim((string)$row[UdfWrapper::PROP_PNR]) !== '';
		$orgus_set = trim((string)$row[UdfWrapper::PROP_ORGUS]) !== '';
		$mail_set = trim((string)$row[UdfWrapper::PROP_EMAIL]) !== '';
		$firstname_set = trim((string)$row[UdfWrapper::PROP_FIRSTNAME]) !== '';
		$lastname_set = trim((string)$row[UdfWrapper::PROP_LASTNAME]) !== '';
		$function_set = trim((string)$row[UdfWrapper::PROP_FUNCTION]) !== '';
		$gender = trim((string)$row[UdfWrapper::PROP_GENDER]);
		$gender_set = $gender === 'm' || $gender === 'f';
		return	$kz_ku_set
			&&	$pnr_set
			&&	$orgus_set
			&&	$mail_set
			&&	$firstname_set
			&&	$lastname_set
			&&	$function_set
			&&	$gender_set;
	}

	protected function postprocessRow(array $row)
	{
		$row[UdfWrapper::PROP_ORGUS] = implode(',',Base\Orgu\ExcelOrgus::normalizedOrguPath($row));
		unset($row[self::COLUMN_LE]);
		unset($row[self::COLUMN_RESSORT]);
		unset($row[self::COLUMN_DEPARTMENT]);
		unset($row[self::COLUMN_GROUP]);
		unset($row[self::COLUMN_TEAM]);
		switch($row[UdfWrapper::PROP_GENDER]) {
			case 'männlich':
				$gender = 'm';
				break;
			case 'weiblich':
				$gender = 'f';
				break;
			default:
				$gender = '';
		}
		$row[UdfWrapper::PROP_PNR] = (string)$row[UdfWrapper::PROP_PNR];
		$row[UdfWrapper::PROP_GENDER] = $gender;
		$row[UdfWrapper::PROP_LOGIN] = trim($row[UdfWrapper::PROP_PNR]);
		if($row[UdfWrapper::PROP_INACTIVE_BEGIN] === '#') {
			$row[UdfWrapper::PROP_INACTIVE_BEGIN] = '';
		}
		if($row[UdfWrapper::PROP_INACTIVE_END] === '#') {
			$row[UdfWrapper::PROP_INACTIVE_END] = '';
		}
		return $row;
	}
}
