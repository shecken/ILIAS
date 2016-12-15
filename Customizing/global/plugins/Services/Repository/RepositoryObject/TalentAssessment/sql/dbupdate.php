<#1>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Settings/ilDB.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Settings/ilDB.php");
$career_goal_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $ilUser, $career_goal_db);
$settings_db->install();
?>

<#2>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Observator/ilDB.php");
$settings_db = new \CaT\Plugins\TalentAssessment\Observator\ilDB($ilDB, $ilUser);
$settings_db->createLocalRoleTemplate(\CaT\Plugins\TalentAssessment\ilActions::OBSERVATOR_ROLE_NAME,"");
?>

<#3>
<?php
global $ilUser;
$b = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Observations\ilDB($ilDB, $ilUser, $b);
$settings_db->install();
?>

<#4>
<?php
global $ilUser;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/Settings/ilDB.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CareerGoal/classes/Settings/ilDB.php");
$career_goal_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $ilUser, $career_goal_db);
$settings_db->install();
?>

<#5>
<?php
global $ilUser;
$b = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $ilUser);
$settings_db = new \CaT\Plugins\TalentAssessment\Observations\ilDB($ilDB, $ilUser, $b);
$settings_db->updateColumns();
?>

<#6>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('xtas');
$new_rbac_options = array(array("view_observations", "View observations", "object", 2700)
						, array("edit_observation", "Edit observation", "object", 2710)
						, array("ta_manager", "Start and finish talent assessment", "object", 2720)
						, array("edit_observator", "Edit observator", "object", 2730)
	);

foreach ($new_rbac_options as $value) {
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation($value[0], $value[1], $value[2], $value[3]);
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
}
?>