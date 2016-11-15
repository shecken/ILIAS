<#1>
<?php
global $ilDB;
require_once("Customizing/global/plugins/Services/Cron/CronHook/EffectivenessAnalysisReminder/classes/EffectivenessAnalysisReminderDB.php");
$db = new EffectivenessAnalysisReminderDB($ilDB);
$db->install();
?>