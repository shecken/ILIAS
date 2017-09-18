<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ReportStudyProgramme/classes/Settings/ilDB.php");
$db = new \CaT\Plugins\ReportStudyProgramme\Settings\ilDB($ilDB, $ilUser);
$db->install();
?>

<#2>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ReportStudyProgramme/classes/Settings/ilDB.php");
$db = new \CaT\Plugins\ReportStudyProgramme\Settings\ilDB($ilDB, $ilUser);
$db->update1();
?>