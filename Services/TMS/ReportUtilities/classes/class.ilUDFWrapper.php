<?php
require_once 'Services/User/classes/class.ilUserDefinedFields.php';

use ILIAS\TMS\ReportUtilities\UDFWrapper;
use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

/**
 * Provide centralized access to ILIAS' UDFs.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-traning.de>
 */
class ilUDFWrapper implements UDFWrapper {
	/**
	 *@var ilUserDefinedFields
	 */
	protected $udf;

	/**
	 * @var	TableRelations\GraphFactory
	 */
	protected $gf;

	/**
	 * @var	TableRelations\TableFactory
	 */
	protected $tf;

	/**
	 * @var	Filter\PredicateFactory
	 */
	protected $pf;

	public function __construct() {
		$this->udf = \ilUserDefinedFields::_getInstance();
		$this->gf = new TableRelations\GraphFactory();
		$this->pf = new Filter\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
	}

	/**
	 * @inheritdoc
	 */
	public function getLUAVisibleFields() {
		$ret = [];
		foreach ($this->udf->getLocalUserAdministrationDefinitions() as $udf_def) {
			$ret[$udf_def['field_id']] = $this->sanitizeName($udf_def['field_name']);
		}
		return $ret;
	}

	/**
	 * Remove unwantedd chars from fieldname.
	 * @param string 	$name
	 * @return string
	 */
	private function sanitizeName($name) {
		return str_replace('-', '', $name);
	}

	/**
	 * @inheritdoc
	 */
	public function appendUDFs($space, $usr_table, $usr_id_field_name) {
		$udf_table = $this->buildBasicUDFTable();
		$eq_field = $usr_table->field($usr_id_field_name);
		$space = $space
			->addTableSecondary($udf_table)
			->addDependency(
				$this->tf->TableJoin(
					$usr_table,
					$udf_table,
					$udf_table->field('usr_id')->EQ($eq_field)
				)
			);

		return $space;
	}

	/**
	 * Setup Space/Table for UDF
	 *
	 * @return Table
	 */
	private function buildBasicUDFTable() {
		//basic UDF setup
		$udf_def = $this->tf->Table('udf_definition', 'udf_def')
			->addField($this->tf->field('field_id'))
			->addField($this->tf->field('field_name'));

		$udf_txt = $this->tf->Table('udf_text', 'udf_txt')
			->addField($this->tf->field('usr_id'))
			->addField($this->tf->field('field_id'))
			->addField($this->tf->field('value'));

		$udf_space = $this->tf->TableSpace()
			->addTablePrimary($udf_txt)
			->addTablePrimary($udf_def)
			->setRootTable($udf_txt)
			->addDependency($this->tf->TableLeftJoin($udf_txt, $udf_def, $udf_def->field('field_id')->EQ($udf_txt->field('field_id'))))
			->request($udf_txt->field('usr_id'));

		//create fields
		// build a query like this to "pivot" udf-text:
		// SELECT  udf_txt.usr_id AS usr_id,
		//     MAX( IF(`udf_txt`.`field_id` = 1 , udf_txt.value,0)) AS UDF_1
		// FROM udf_text AS udf_txt
		//     LEFT JOIN udf_definition AS udf_def ON (`udf_def`.`field_id` = `udf_txt`.`field_id` )
		// GROUP BY udf_txt.usr_id

		$udf_nullfield = $this->tf->constInt('nullval', 0);
		$udf_fields = [];
		foreach ($this->getLUAVisibleFields() as $udf_field_id => $udf_field_name) {
			$udf_fid = $this->pf->int($udf_field_id);
			$udf_fields[] = $this->tf->max(
				$udf_field_name,
				$this->tf->ifThenElse(
					'internal_udf_'.$udf_field_id, //name
					$udf_txt->field('field_id')->EQ($udf_fid),
					$udf_txt->field('value'),
					$udf_nullfield
				)
			);
		}

		//apply to space
		foreach ($udf_fields as $udf_field) {
			$udf_space = $udf_space->request($udf_field);
		}
		$udf_space = $udf_space->groupBy($udf_txt->field('usr_id'));
		$udf_table = $this->tf->derivedTable($udf_space, 'udf');

		return $udf_table;
	}


}