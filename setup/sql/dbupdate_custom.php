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

<#9>
<?php
global $DIC;
$role_root_folder = 8;

require_once("Services/TMS/Roles/classes/class.ilTMSRolesDB.php");
$tms_settings_db = new ilTMSRolesDB($ilDB);
$query = "SELECT rol_id FROM rbac_fa WHERE parent = $role_root_folder AND assign='y'";
$res = $ilDB->query($query);
while($row = $ilDB->fetchAssoc($res)) {
	$tms_settings = $tms_settings_db->selectFor((int)$row["rol_id"]);
	$tms_settings_db->update($tms_settings);
}
?>

<#10>
<?php
	// cat-tms-patch start
	$ilCtrlStructureReader->getStructure();
	// cat-tms-patch end
?>

<#11>
<?php
	global $DIC;
	require_once("Services/Tree/classes/class.ilTree.php");
	$tree = new ilTree(0);
	require_once("Services/Object/classes/class.ilObjectDataCache.php");
	$cache = new ilObjectDataCache();
	$provider_db = new CaT\Ente\ILIAS\ilProviderDB($DIC->database(), $tree, $cache);
	$provider_db->createTables();
?>

<#12>
<?php
require_once("Services/Tree/classes/class.ilTree.php");
$tree = new ilTree(0);
require_once("Services/Object/classes/class.ilObjectDataCache.php");
$cache = new ilObjectDataCache();
require_once("Services/Object/classes/class.ilObject.php");

$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $tree, $cache);
$query = "SELECT od.obj_id FROM object_data od JOIN object_reference oref ON oref.obj_id = od.obj_id WHERE od.type = 'crs' AND oref.deleted IS NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
	$obj = new ilObject();
	$obj->setId($row["obj_id"]);
	foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
		$provider_db->update($provider);
	}
}
?>