<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ReportVAPass/classes/Settings/ilDB.php");
$db = new \CaT\Plugins\ReportVAPass\Settings\ilDB($ilDB, $ilUser);
$db->install();
?>
