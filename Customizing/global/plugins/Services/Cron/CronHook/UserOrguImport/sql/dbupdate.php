<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xuoi_ext_role_config')) {
	$columns =
		[
			'ext_role_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
			'role_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true]
		];
	$db->createTable('xuoi_ext_role_config', $columns);
}
?>

<#2>
<?php
global $DIC;
$db = $DIC['ilDB'];

$db->addPrimaryKey('xuoi_ext_role_config', ['ext_role_id', 'role_id']);
?>

<#3>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xuoi_ext_roles')) {
	$columns =
		[
			'ext_role' => ['type' => 'text', 'length' => 128, 'notnull' => true]
		];
	$db->createTable('xuoi_ext_roles', $columns);
}
?>



<#4>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('xuoi_ext_roles', 'description')) {
	$db->addTableColumn('xuoi_ext_roles', 'description', ['type' => 'text', 'length' => 512, 'notnull' => false]);
}
?>

<#5>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('xuoi_ext_roles', 'id')) {
	$db->addTableColumn('xuoi_ext_roles', 'id', ['type' => 'integer', 'length' => 4, 'notnull' => true]);
}
$db->createSequence('xuoi_ext_roles');
?>

<#6>
<?php
global $DIC;
$db = $DIC['ilDB'];
$db->addPrimaryKey('xuoi_ext_roles', ['id']);
?>

<#7>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xuoi_uoi_log')) {
		$columns = [
				'id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
				'entry' => ['type' => 'text', 'length' => 768, 'notnull' => true],
				'timestamp' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
				'orgu_id' => ['type' => 'text', 'length' => 64],
				'aob' => ['type' => 'text', 'length' => 64],
				'pnr' => ['type' => 'text', 'length' => 64]
			];
		$db->createTable('xuoi_uoi_log', $columns);
}
?>

<#8>
<?php
global $DIC;
$db = $DIC['ilDB'];
$this->db->createSequence('xuoi_uoi_log');
?>

<#9>
<?php
global $DIC;
$db = $DIC['ilDB'];
$db->addPrimaryKey('xuoi_uoi_log', ['id']);
?>

<#10>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xuoi_user_orgu_config')) {
		$columns = [
				'function' => ['type' => 'text', 'length' => 64, 'notnull' => true],
				'role' => ['type' => 'text', 'length' => 64, 'notnull' => true]
			];
		$db->createTable('xuoi_user_orgu_config', $columns);
}
?>

<#11>
<?php
global $DIC;
$db = $DIC['ilDB'];
$db->addPrimaryKey('xuoi_user_orgu_config', ['function']);
?>

<#12>
<?php
require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
$permissions =
		["visible"				=> true
		,"changeable"			=> false
		,"searchable"			=> true
		,"required"				=> false
		,"export"				=> true
		,"course_export"		=> false
		,"group_export"			=> false
		,"registration_visible"	=> false
		,"visible_lua"			=> false
		,"changeable_lua"		=> false
		,"certificate"			=> false];

gevUDFUtils::updateUDFFields([
	gevSettings::USR_UDF_FINANCIAL_ACCOUNT => ["Kostenstelle",$permissions,null]
	,gevSettings::USR_UDF_ENTRY_DATE => ["Eintrittsdatum KU",$permissions,null]]);
?>

<#13>
<?php
require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
$permissions = [
		"visible"				=> true
		,"changeable"			=> false
		,"searchable"			=> true
		,"required"				=> false
		,"export"				=> true
		,"course_export"		=> false
		,"group_export"			=> false
		,"registration_visible"	=> false
		,"visible_lua"			=> false
		,"changeable_lua"		=> false
		,"certificate"			=> false];

$fields = [	 'Kennzeichen KU' => [ gevSettings::USR_UDF_FLAG_KU,UDF_TYPE_TEXT,$permissions,null]
			,'Organisationseinheit SAP'=> [ gevSettings::USR_UDF_ORGU_SAP,UDF_TYPE_TEXT,$permissions,null]
			,'Personalnummer' => [ gevSettings::USR_UDF_PERSONAL_ID,UDF_TYPE_TEXT,$permissions,null]
			,'Bezeichnung Kostenstelle' => [gevSettings::USR_UDF_FINANCIAL_ACCOUNT_LONG,UDF_TYPE_TEXT,$permissions,null]
			,'Funktion' => [ gevSettings::USR_UDF_FUNCTION ,UDF_TYPE_TEXT,$permissions,null]
			,'Eintrittsdatum KO' => [ gevSettings::USR_UDF_ENTRY_DATE_KO,UDF_TYPE_TEXT,$permissions,null]
			,'inaktiv von' => [gevSettings::USR_UDF_INACTIVE_START ,UDF_TYPE_TEXT,$permissions,null]
			,'inaktiv bis' => [gevSettings::USR_UDF_INACTIVE_END,UDF_TYPE_TEXT,$permissions,null]
			,'Vorgesetzter' => [gevSettings::USR_UDF_SUPERIOR_OF_USR ,UDF_TYPE_TEXT,$permissions,null]
			];
gevUDFUtils::createUDFFields($fields);
?>