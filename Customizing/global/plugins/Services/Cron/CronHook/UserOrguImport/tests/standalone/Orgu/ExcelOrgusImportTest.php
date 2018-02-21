<?php

use CaT\IliasUserOrguImport\Orgu\ExcelOrgus as EO;
use CaT\IliasUserOrguImport\Orgu\OrguAMDWrapper as OAW;
use CaT\IliasUserOrguImport\Orgu\OrguIdentifier as OrguIdentifier;

class ExcelOrgusPathImportTest extends PHPUnit_Framework_TestCase
{

	protected function getExcelOrgus()
	{
		return new ExcelOrgusProtectedAccess(new OrguIdentifier());
	}

	public function test_distinct_orgu_paths()
	{
		$extracted =
			[[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd1', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 't1']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd1', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 'keine Zuordnung']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd2', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 't1']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r2', EO::COLUMN_DEPARTMENT => 'd3', EO::COLUMN_GROUP => 'd3', EO::COLUMN_TEAM => 'Nicht zugeordnet']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd1', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 'keine Zuordnung']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd2', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 't1']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r2', EO::COLUMN_DEPARTMENT => 'd3', EO::COLUMN_GROUP => 'd3', EO::COLUMN_TEAM => 'Keine Beschreibung']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd1', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 'keine Zuordnung']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r1', EO::COLUMN_DEPARTMENT => 'd2', EO::COLUMN_GROUP => 'g1', EO::COLUMN_TEAM => 't1']
			,[EO::COLUMN_LE => 'le1', EO::COLUMN_RESSORT => 'r2', EO::COLUMN_DEPARTMENT => 'd3', EO::COLUMN_GROUP => 'd3', EO::COLUMN_TEAM => 'keine Zuordnung']
			,[EO::COLUMN_LE => 'le2', EO::COLUMN_RESSORT => 'le2', EO::COLUMN_DEPARTMENT => 'le2', EO::COLUMN_GROUP => 'le2', EO::COLUMN_TEAM => 'le2']
			,[EO::COLUMN_LE => 'le3', EO::COLUMN_RESSORT => 'keine Zuordnung', EO::COLUMN_DEPARTMENT => 'keine Zuordnung', EO::COLUMN_GROUP => 'keine Zuordnung', EO::COLUMN_TEAM => 'keine Zuordnung']
			];

		$expected =
			[['le1', 'r1', 'd1', 'g1', 't1']
			,['le1', 'r1', 'd1', 'g1']
			,['le1', 'r1','d1']
			,['le1', 'r1']
			,['le1']
			,['le1', 'r1', 'd2', 'g1', 't1']
			,['le1', 'r1', 'd2', 'g1']
			,['le1', 'r1', 'd2']
			,['le1', 'r2', 'd3']
			,['le1', 'r2']
			,['le2']
			,['le3']
			];
		$this->assertEquals(array_values($this->getExcelOrgus()->_distinctOrguPaths($extracted)), $expected);
	}

	public function test_orgu_by_path()
	{

		$path = [EO::COLUMN_LE => 'some1',EO::COLUMN_RESSORT => 'some2', EO::COLUMN_DEPARTMENT => 'some3', EO::COLUMN_GROUP => 'some4'];
		$orgu = $this->getExcelOrgus()->_orguByOrguPath($path);
		$properties = $orgu->properties();
		$parent_properties = $orgu->parentOrguIdProperties();
		$this->assertEquals($properties[OAW::PROP_ID], md5(implode('/', $path)));
		$this->assertEquals($properties[OAW::PROP_TITLE], array_pop($path));
		$this->assertEquals($parent_properties[OAW::PROP_ID], (md5(implode('/', $path))));
	}
}

class ExcelOrgusProtectedAccess extends EO
{
	public function __construct(OrguIdentifier $ident)
	{
		$this->ident = $ident;
	}

	public function _distinctOrguPaths(array $exctracted)
	{
		return $this->distinctOrguPaths($exctracted);
	}

	public function _orguByOrguPath(array $path)
	{
		return $this->orguByOrguPath($path);
	}
}
