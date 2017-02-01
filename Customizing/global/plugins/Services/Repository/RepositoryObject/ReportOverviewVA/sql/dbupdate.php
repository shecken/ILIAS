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
	
if(!$ilDB->tableExists("rep_robj_ova")) {
	$ilDB->createTable("rep_robj_ova", $fields);
	$ilDB->addPrimaryKey("rep_robj_ova", array("id"));
}
?>