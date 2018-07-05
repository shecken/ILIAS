<?php

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

/**
 * Provide access to ILIAS' UDFs.
 *
 * getLUAVisibleFields will return a dict (field_id=>field_name) of UDFs.
 * To use this, setup your space and append UDFs with appendUDFs();
 * this will extend $space with a DerivedTable (id='udf').
 * Example:
 *		$space = $this->appendUDFs(
 *			$this->tf, $this->pf,
 *			$space, $usr_data, 'usr_id'
 *		);
 *
 * For output, you will have to adjust the row-template;
 * add a generic block like this:
 *		<!-- BEGIN udf_block -->
 *			<td>{VAL_UDFFIELD}</td>
 *		<!-- END udf_block -->
 *
 * Finally, you will have to define colums (defineFieldColumn):
 *  	$column_id = 'UDF_' .(string)$field_id;
 *		$table = $table->defineFieldColumn(
 *			$fieldname,
 *			$column_id,
 *			[$column_id => $space->table('udf')->field($fieldname)]
 *			,true
 *		);
 *
 * Note, that the column-id starts with 'UDF_'.
 * The fillRow-method will check for this and touch the udf_block.
 * This way, there is no need to revise the template when adding fields.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-traning.de>
 */
trait ilUDFWrapper {

	/**
	 * @inheritdoc
	 */
	public function getLUAVisibleFields() {
		require_once 'Services/User/classes/class.ilUserDefinedFields.php';
		$il_udf = \ilUserDefinedFields::_getInstance();
		$ret = [];
		foreach ($il_udf->getLocalUserAdministrationDefinitions() as $udf_def) {
			$ret[$udf_def['field_id']] = $this->sanitizeUDFName($udf_def['field_name']);
		}
		return $ret;
	}

	/**
	 * Remove unwanted chars from fieldname.
	 * @param string 	$name
	 * @return string
	 */
	private function sanitizeUDFName($name) {
		return str_replace('-', '', $name);
	}

	/**
	 * @inheritdoc
	 */
	public function appendUDFs(
		TableRelations\TableFactory $tf,
		Filter\PredicateFactory $pf,
		TableRelations\Tables\TableSpace $space,
		TableRelations\Tables\Table $usr_table,
		$usr_id_field_name
	) {
		assert('is_string($usr_id_field_name)');
		$udf_table = $this->buildBasicUDFTable($tf, $pf);
		$eq_field = $usr_table->field($usr_id_field_name);
		$space = $space
			->addTableSecondary($udf_table)
			->addDependency(
				$tf->TableJoin(
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
	 * @return TableRelations\Tables\DerivedTable
	 */
	private function buildBasicUDFTable(
		TableRelations\TableFactory $tf,
		Filter\PredicateFactory$pf
	) {
		//basic UDF setup
		$udf_def = $tf->Table('udf_definition', 'udf_def')
			->addField($tf->field('field_id'))
			->addField($tf->field('field_name'));

		$udf_txt = $tf->Table('udf_text', 'udf_txt')
			->addField($tf->field('usr_id'))
			->addField($tf->field('field_id'))
			->addField($tf->field('value'));

		$udf_space = $tf->TableSpace()
			->addTablePrimary($udf_txt)
			->addTablePrimary($udf_def)
			->setRootTable($udf_txt)
			->addDependency($tf->TableLeftJoin($udf_txt, $udf_def, $udf_def->field('field_id')->EQ($udf_txt->field('field_id'))))
			->request($udf_txt->field('usr_id'));

		//create fields
		// build a query like this to "pivot" udf-text:
		// SELECT  udf_txt.usr_id AS usr_id,
		//     MAX( IF(`udf_txt`.`field_id` = 1 , udf_txt.value,0)) AS UDF_1
		// FROM udf_text AS udf_txt
		//     LEFT JOIN udf_definition AS udf_def ON (`udf_def`.`field_id` = `udf_txt`.`field_id` )
		// GROUP BY udf_txt.usr_id

		$udf_nullfield = $tf->constString('nullval', '');
		$udf_fields = [];
		foreach ($this->getLUAVisibleFields() as $udf_field_id => $udf_field_name) {
			$udf_fid = $pf->int($udf_field_id);
			$udf_fields[] = $tf->max(
				$udf_field_name,
				$tf->ifThenElse(
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
		$udf_table = $tf->derivedTable($udf_space, 'udf');

		return $udf_table;
	}

}