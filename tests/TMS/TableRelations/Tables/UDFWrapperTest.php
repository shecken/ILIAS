<?php
use ILIAS\TMS\TableRelations as TableRelations;
use ILIAS\TMS\Filter as Filters;

require_once 'Services/TMS/ReportUtilities/classes/class.ilUDFWrapper.php';

class _TestReport{

	use \ilUDFWrapper;

	public function __construct(){
		$this->gf = new TableRelations\GraphFactory();
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, $this->gf);

		$this->table = $this->tf->Table("table", "table_id",
			array($this->tf->Field("field1")
				, $this->tf->Field("field2")
			)
		);
		$this->usr_table = $this->tf->Table("usr_table", "usr")
			->addField($this->tf->field("usr_id"));


		$this->space = $this->tf->TableSpace()
			->addTablePrimary($this->table)
			->addTableSecondary($this->usr_table)
			->setRootTable($this->table)
			->request($this->usr_table->field('usr_id'))
			;
	}

	public function _appendUDFsToSpace() {
		return $this->appendUDFsToSpace($this->tf, $this->pf, $this->space, $this->usr_table, 'usr_id');
	}

	public function _addUDFColumnsToTable(
		TableRelations\Tables\TableSpace $space,
		\SelectableReportTableGUI $table
	) {
		return $this->addUDFColumnsToTable($space, $table);
	}

	protected function getFieldsVisibleInCourseMemberAdministration() {
		return [
			12 => ["i12", "twelve"],
			15 => ["i15", "fifteen"]
		];
	}
}

class _testSelectableReportTableGUI extends \SelectableReportTableGUI {
	public $selectable;
	public function __construct() {

	}
}


class UDFWrapperTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->tr = new _TestReport();
	}

	public function test_addingToSpace() {
		$space = $this->tr->_appendUDFsToSpace('usr_id');
		$this->assertInstanceOf(
			"\\ILIAS\\TMS\\TableRelations\\Tables\\DerivedTable",
			$space->table('udf')
		);
	}
	public function test_addingColumns() {
		$space = $this->tr->_appendUDFsToSpace('usr_id');
		$table = new _testSelectableReportTableGUI();
		$table = $this->tr->_addUDFColumnsToTable($space, $table);

		$this->assertEquals(
			array(
				'UDF_12',
				'UDF_15',
			),
			array_keys($table->selectable)
		);
		$this->assertEquals(
			'twelve',
			$table->selectable['UDF_12']['txt']
		);
	}
}
