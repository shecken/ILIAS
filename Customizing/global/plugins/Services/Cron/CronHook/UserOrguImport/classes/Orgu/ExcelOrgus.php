<?php

namespace CaT\IliasUserOrguImport\Orgu;

use CaT\UserOrguImport\Orgu\AdjacentOrgUnits as Orgus;
use CaT\UserOrguImport\Orgu\AdjacentlyConstructedOS as OrgStruct;
use CaT\UserOrguImport\Orgu\AdjacentOrgUnit as AdjacentOrgu;
use CaT\UserOrguImport\Orgu\OrgUnit as Orgu;
use CaT\IliasUserOrguImport\ErrorReporting\ErrorCollection as ErrorCollection;
use CaT\IliasUserOrguImport\Filesystem\ImportFiles as ImportFiles;
use CaT\IliasUserOrguImport\Data\DataExtractor as DataExtractor;

/**
 * Extract orgu information from a data source.
 */
class ExcelOrgus
{

	const COLUMN_LE = 'le';
	const COLUMN_RESSORT = 'ressort';
	const COLUMN_DEPARTMENT = 'departement';
	const COLUMN_GROUP = 'group';
	const COLUMN_TEAM = 'team';

	protected static $NO_ASSIGNMENT = [
		'Keine Zuordnung',
		'keine Zuordnung',
		'Nicht zugeordnet',
		'nicht zugeordnet',
		'Keine Beschreibung',
		'keine Beschreibung'
	];

	protected $orgu_id_set = [];

	public function __construct(ImportFiles $import_files, DataExtractor $extractor, OrguIdentifier $ident, ErrorCollection $ec)
	{
		$this->import_files = $import_files;
		$this->extractor = $extractor;
		$this->ident = $ident;
		$this->ec = $ec;
	}

	public static $conversions = [
			'LE' => self::COLUMN_LE,
			'RESSORT' => self::COLUMN_RESSORT,
			'ABTEILUNG' => self::COLUMN_DEPARTMENT,
			'GRUPPE' => self::COLUMN_GROUP,
			'TEAM' => self::COLUMN_TEAM
			];

	/**
	 * Get an array of orgu data.
	 * Delivered data will be checked and lacks faulty
	 * entries from file. Returns null at fatal error.
	 *
	 * @return	mixed[][]|null
	 */
	public function orgus()
	{
		if ($file_path = $this->import_files->getCurrentOrguFilePath()) {
			$orgus = $this->extractOrgus($file_path);
			if (!$orgus) {
				return null;
			}
		} else {
			$this->ec->addError('Orgu import error');
			return null;
		}

		$os = new OrgStruct($this->ident);
		$os->addRootOrgu(new Orgu([OrguAMDWrapper::PROP_ID => OrguConfig::ROOT_ID],$this->ident));
		foreach ($orgus as $orgu) {
			try {
				$os->addOrgu($orgu);
			} catch (\LogicException $e) {
				$this->ec->addError('Exception at building orgu tree: '.$e->getMessage());
				return null;
			}
		}
		if ($os->treeConsistent()) {
			return $orgus;
		} else {
			$this->ec->addError('Tree inconsistent');
			return null;
		}
	}

	/**
	 * Add a whole subtree to $orgus.
	 *
	 */
	protected function extractOrgus($file_path)
	{
		assert('is_string($file_path)');
		$orgus = new Orgus($this->ident);

		foreach($this->distinctOrguPaths(
					$this->extractor->extractContent(
						$this->import_files->getCurrentOrguFilePath()
						,self::$conversions)) as $orgu_path) {
			$aux = null;

			$orgu_path = $this->preprocessRow($orgu_path);

			foreach ($orgu_path as $level) {
				if($aux === $level || in_array($level, self::$NO_ASSIGNMENT)) {
					break;
				}
				$aux = $level;
			}
			$orgus->add($this->orguByOrguPath($orgu_path,$orgus));
		}
		return $orgus;
	}

	protected function preprocessRow(array $row) {
		$ret = [];
		foreach ($row as $key => $value) {
			$ret[$key] = trim($value);
		}
		return $ret;
	}

	protected function postprocessRow(array $row, $root_id)
	{
		return $row;
	}

	protected function checkRow(array $row)
	{
		return true;
	}

	protected function distinctOrguPaths(array $extracted)
	{
		$orgu_paths = [];
		foreach ($extracted as $row) {
			$row_aux = self::normalizedOrguPath($row);
			if(count($row_aux) === 0) {
				continue;
			}
			do {
				if(count($row_aux) === 0) {
					break;
				}
				$orgu_path_string = implode('-', $row_aux);
				if(isset($orgu_paths[$orgu_path_string])) {
					break;
				}
				$orgu_paths[$orgu_path_string] = $row_aux;
			} while(array_pop($row_aux));
		}
		return $orgu_paths;
	}

	protected function orguByOrguPath(array $orgu_path)
	{
		$import_id = self::idByOrguPath($orgu_path);
		$title = array_pop($orgu_path);

		if(count($orgu_path) === 0) {
			$parent_id = OrguConfig::ROOT_ID;
		} else {
			$parent_id = self::idByOrguPath($orgu_path);
		}
		$properties = [	OrguAMDWrapper::PROP_TITLE => $title,
						OrguAMDWrapper::PROP_ID => $import_id];
		$parent_properties = [OrguAMDWrapper::PROP_ID => $parent_id];
		return new AdjacentOrgu($properties,$this->ident,$parent_properties);
	}

	public static function normalizedOrguPath(array $path)
	{
		$aux = null;
		$path_aux = [];
		foreach (self::$conversions as $level) {
			$current = $path[$level];
			if($current === $aux || in_array($current, self::$NO_ASSIGNMENT) || trim((string)$current) === '') {
				break;
			}
			$aux = $current;
			$path_aux[] = $current;
		}
		return $path_aux;
	}

	public static function idByOrguPath(array $path)
	{
		return md5(implode('/',$path));
	}
}
