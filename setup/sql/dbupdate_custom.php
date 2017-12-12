<#1>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createTable();
?>

<#2>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createPrimaryKey();
?>

<#3>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createSequence();
?>

<#4>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createParamsTable();
?>
