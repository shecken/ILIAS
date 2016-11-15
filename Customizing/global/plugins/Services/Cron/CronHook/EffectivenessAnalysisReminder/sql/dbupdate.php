<#1>
<?php
global $ilDB;
require_once("Customizing/global/plugins/Services/Cron/CronHook/EffectivenessAnalysisReminder/classes/ilEffectivenessAnalysisReminderDB.php");
$db = new ilEffectivenessAnalysisReminderDB($ilDB);
$db->install();
?>