<#1>
<?php
global $DIC;
require_once("Customizing/global/plugins/Services/Cron/CronHook/DiMAkImport/vendor/autoload.php");
$db = new \CaT\Plugins\DiMAkImport\Import\Data\ilDB($DIC["ilDB"]);
$db->createTable();
?>
<#2>
<?php
global $DIC;
require_once("Customizing/global/plugins/Services/Cron/CronHook/DiMAkImport/vendor/autoload.php");
$db = new \CaT\Plugins\DiMAkImport\Import\Data\ilDB($DIC["ilDB"]);
$db->createPrimaryKey();
?>