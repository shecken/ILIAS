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

<#2>
<?php
$ilDB->renameTableColumn('crs_copy_mappings', "obj_id", "obj_id2");
$ilDB->renameTableColumn('crs_copy_mappings', "source_id", "obj_id");
$ilDB->renameTableColumn('crs_copy_mappings', "obj_id2", "source_id");
?>

<#3>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
$db = new Schedule($ilDB);
$db->createTable();
?>

<#4>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
$db = new Schedule($ilDB);
$db->createPrimaryKey();
?>

<#5>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
$db = new Schedule($ilDB);
$db->createSequence();
?>

<#6>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
$db = new Schedule($ilDB);
$db->createParamsTable();
?>

<#7>
<?php
if( !$ilDB->tableExists('tms_role_settings') )
{
	$ilDB->createTable('tms_role_settings', array(
		'role_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'hide_breadcrumb' => array(
			'type' => 'integer',
			'length' => 1,
			'default' => 0
		),
		'hide_menu_tree' => array(
			'type' => 'integer',
			'length' => 1,
			'default' => 0
		)
	));
}
?>

<#8>
<?php
if($ilDB->tableExists('tms_role_settings') )
{
	$ilDB->addPrimaryKey('tms_role_settings', array('role_id'));
}
?>