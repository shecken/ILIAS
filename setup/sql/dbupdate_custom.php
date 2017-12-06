<#1>
<?php

if( !$ilDB->tableExists('crs_copy_mappings') )
{
	$ilDB->createTable('crs_copy_mappings', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'source_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('crs_copy_mappings', array('obj_id', 'source_id'));
}
?>
