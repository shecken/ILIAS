<#1>
<?php
$fields =
	array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	);

if (!$ilDB->tableExists("rep_robj_xspo")) {
	$ilDB->createTable("rep_robj_xspo", $fields);
	$ilDB->addPrimaryKey("rep_robj_xspo", array("id"));
}
?>

<#2>
<?php
if (!$ilDB->tableColumnExists("rep_robj_xspo", "selected_study_prg")) {
	$ilDB->addTableColumn('rep_robj_xspo', 'selected_study_prg', array(
				'type' => 'text',
				'length' => 255,
				'notnull' => false
				));
}
?>