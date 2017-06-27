<#1>
<?php
$fields =
	array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'is_online' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		)
	);
if (!$ilDB->tableExists("rep_robj_rea34i")) {
	$ilDB->createTable("rep_robj_rea34i", $fields);
	$ilDB->addPrimaryKey("rep_robj_rea34i", array("id"));
}
?>

<#2>
<?php
	$field_data = [
			'type' => 'integer',
			'length' => 1,
			'notnull' => false];

	if (!$ilDB->tableColumnExists("rep_robj_rea34i", 'is_local')) {
		$ilDB->addTableColumn("rep_robj_rea34i", 'is_local', $field_data);
	}
?>