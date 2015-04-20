<#1>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

$fields = array( "Unternehmensname" => array( gevSettings::USR_UDF_COMPANY_NAME
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Vertriebsregion/Sales region/销售区域" => array( gevSettings::USR_UDF_SALES_REGION
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Eintrittsdatum" => array( gevSettings::USR_UDF_ENTRY_DATE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Austrittsdatum" => array( gevSettings::USR_UDF_EXIT_DATE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   );

gevUDFUtils::createUDFFields($fields);

// Move the current data to the UDF-fields under my control. Harharhar.
global $ilDB;
$sales_region_id = gevSettings::getInstance()->getUDFFieldId(gevSettings::USR_UDF_SALES_REGION);
$ilDB->manipulate("UPDATE udf_text SET field_id = ".$ilDB->quote($sales_region_id, "integer")." WHERE field_id = 1");

$entry_date_id = gevSettings::getInstance()->getUDFFieldId(gevSettings::USR_UDF_ENTRY_DATE);
$ilDB->manipulate("UPDATE udf_text SET field_id = ".$ilDB->quote($entry_date_id, "integer")." WHERE field_id = 2");

// Remove old fields.
require_once("Services/User/classes/class.ilUDFClaimingPlugin.php");
ilUDFClaimingPlugin::deleteDBField(1);
ilUDFClaimingPlugin::deleteDBField(2);


?>