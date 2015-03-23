<#1>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

$tselect = ilAdvancedMDFieldDefinition::TYPE_SELECT;
$ttext = ilAdvancedMDFieldDefinition::TYPE_TEXT;
$tdate = ilAdvancedMDFieldDefinition::TYPE_DATE;
$tdatetime = ilAdvancedMDFieldDefinition::TYPE_DATETIME;
$tinteger = ilAdvancedMDFieldDefinition::TYPE_INTEGER;
$tfloat = ilAdvancedMDFieldDefinition::TYPE_FLOAT;
$tlocation = ilAdvancedMDFieldDefinition::TYPE_LOCATION;
$tmultiselect = ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT;

$gev_set = gevSettings::getInstance();

$records_org = 
array( "Address"
		=> 	array( "Address of Organizational Unit", 
			array( "Street" => 
						array( gevSettings::ORG_AMD_STREET			# 0 to save in settings
							 , null									# 1 description
							 , true 								# 2 searchable
							 , null 								# 3 definition
							 , $ttext 								# 4 type
							 )
				 , "House Number" =>
				 		array( gevSettings::ORG_AMD_HOUSE_NUMBER
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "Zipcode" =>
				 		array( gevSettings::ORG_AMD_ZIPCODE
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "City" =>
				 		array( gevSettings::ORG_AMD_CITY
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 ))
	 , "Contact Information" 
		=> array( "Contact Person in the Organizational Unit",
		   array( "Name" =>
				 		array( gevSettings::ORG_AMD_CONTACT_NAME
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				, "Telephone Number" =>
						array( gevSettings::ORG_AMD_CONTACT_PHONE
							 , null
							 , true
							 , null
							 , $ttext
							 )
				, "Fax Number" =>
						array( gevSettings::ORG_AMD_CONTACT_FAX
							 , null
							 , true
							 , null
							 , $ttext
							 )
				, "E-Mail" =>
						array( gevSettings::ORG_AMD_CONTACT_EMAIL
							 , null
							 , true
							 , null
							 , $ttext
							 )
				, "Homepage" =>
						array( gevSettings::ORG_AMD_HOMEPAGE
							 , null
							 , true
							 , null
							 , $ttext
							 )
				))
	);




$records_venue = 
array("Location"
	 	=> array(null,
	 	   array( "Location" =>
	 	   				array( gevSettings::VENUE_AMD_LOCATION
	 	   					 , null
	 	   					 , false
	 	   					 , null
	 	   					 , $tlocation
	 	   					 )
	 	   		))
	 , "Pricing"
		=> array( "Prices for Accomodation and Catering",
		   array( "Price per Overnight Stay" => 
		   				array( gevSettings::VENUE_AMD_COSTS_PER_ACCOM
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Breakfast All Inclusive" => 
		   				array( gevSettings::VENUE_AMD_COSTS_BREAKFAST
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Dinner All Inclusive" => 
		   				array( gevSettings::VENUE_AMD_COSTS_LUNCH
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Coffee Break All Inclusive" => 
		   				array( gevSettings::VENUE_AMD_COSTS_COFFEE
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Dinner All Inclusive" => 
		   				array( gevSettings::VENUE_AMD_COSTS_DINNER
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Catering All Inclusive" => 
		   				array( gevSettings::VENUE_AMD_COSTS_FOOD
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
		   		, "Overnight All Inclusive" =>
		   				array( gevSettings::VENUE_AMD_COSTS_HOTEL
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
		   		, "Overnight and Catering All Inclusive" =>
		   				array( gevSettings::VENUE_AMD_ALL_INCLUSIVE_COSTS
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
		   ))
	);

$records_org_ids = gevAMDUtils::createAMDRecords($records_org, array(array("orgu", "orgu_type")));

$records_venue_ids = gevAMDUtils::createAMDRecords($records_venue, array(array("orgu", "orgu_type")));

require_once("Customizing/global/plugins/Modules/OrgUnit/OrgUnitTypeHook/GEVOrgTypes/classes/class.ilGEVOrgTypesPlugin.php");

// This is hacky!
ilGEVOrgTypesPlugin::$allow = true;

gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_VENUE, $records_org_ids);
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_VENUE, $records_venue_ids);
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_PROVIDER, $records_org_ids);
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_DEFAULT, $records_org_ids);

ilGEVOrgTypesPlugin::$allow = false;
?>

<#2>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
$ttext = ilAdvancedMDFieldDefinition::TYPE_TEXT;

$records_org_default = 
array( "Cost Center"
		=> 	array( "Cost Center of Organizational Unit", 
			array( "Cost Center" => 
						array( gevSettings::ORG_AMD_FINANCIAL_ACCOUNT	# 0 to save in settings
							 , null									# 1 description
							 , true 								# 2 searchable
							 , null 								# 3 definition
							 , $ttext 								# 4 type
							 )
			))
);

$records_org_ids_default_units = gevAMDUtils::createAMDRecords($records_org_default, array(array("orgu", "orgu_type")));

require_once("Customizing/global/plugins/Modules/OrgUnit/OrgUnitTypeHook/GEVOrgTypes/classes/class.ilGEVOrgTypesPlugin.php");
ilGEVOrgTypesPlugin::$allow = true;
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_DEFAULT, $records_org_ids_default_units);
ilGEVOrgTypesPlugin::$allow = false;

?>
