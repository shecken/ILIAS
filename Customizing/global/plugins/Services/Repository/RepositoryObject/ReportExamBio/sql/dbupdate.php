<#1>
<?php
if(!$ilDB->tableExists("rep_robj_rexbio")) {
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
	 
	$ilDB->createTable("rep_robj_rexbio", $fields);
	$ilDB->addPrimaryKey("rep_robj_rexbio", array("id"));
}
?>