<#1>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$tselect = ilAdvancedMDFieldDefinition::TYPE_SELECT;
$ttext = ilAdvancedMDFieldDefinition::TYPE_TEXT;
$tdate = ilAdvancedMDFieldDefinition::TYPE_DATE;
$tdatetime = ilAdvancedMDFieldDefinition::TYPE_DATETIME;
$tinteger = ilAdvancedMDFieldDefinition::TYPE_INTEGER;
$tfloat = ilAdvancedMDFieldDefinition::TYPE_FLOAT;
$tlocation = ilAdvancedMDFieldDefinition::TYPE_LOCATION;
$tmultiselect = ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT;
$tvenue = ilAdvancedMDFieldDefinition::TYPE_VENUE_SELECT;
$tprovider = ilAdvancedMDFieldDefinition::TYPE_PROVIDER_SELECT;
$tlongtext = ilAdvancedMDFieldDefinition::TYPE_LONG_TEXT;
$tschedule = ilAdvancedMDFieldDefinition::TYPE_SCHEDULE;
$tteporgu = ilAdvancedMDFieldDefinition::TYPE_TEP_ORGU_SELECT;

$gev_set = gevSettings::getInstance();

$records = 
array( "Period"
	 	=> array(null,
	 	   array( "Start-Date" =>	
	 	   				array( gevSettings::CRS_AMD_START_DATE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tdate
	 	   					 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
	 	   					 )
	 	   		, "End-Date" =>
	 	   				array( gevSettings::CRS_AMD_END_DATE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tdate
	 	   					 )
	 	   		, "Schedule" =>
	 	   				array( gevSettings::CRS_AMD_SCHEDULE
	 	   					 , null
	 	   					 , false
	 	   					 , null
	 	   					 , $tschedule
	 	   					 )
				, "Planned for" =>
						array( gevSettings::CRS_AMD_SCHEDULED_FOR
							 , ""
							 , false
							 , null
							 , $ttext
							 )
	 	   		))
	 , "Locations and Provider"
	 	=> array( null, 
	 	   array( "Provider" =>
	 	   				array( gevSettings::CRS_AMD_PROVIDER
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tprovider
	 	   					 )
	 	   		, "Venue" =>
	 	   				array( gevSettings::CRS_AMD_VENUE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tvenue
	 	   					 )
	 	   		, "Hotel Accomodation" =>
	 	   				array( gevSettings::CRS_AMD_ACCOMODATION
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tvenue
	 	   					 )
				, "Webinar Link" =>
						array( gevSettings::CRS_AMD_WEBINAR_LINK
							 , null
							 , false
							 , null
							 , $ttext
							 )
				, "Webinar Password" =>
						array( gevSettings::CRS_AMD_WEBINAR_PASSWORD
							 , ""
							 , false
							 , null
							 , $ttext
	 	   					 )
				, "Organizational" =>
						array( gevSettings::CRS_AMD_ORGA
							 , null
							 , false
							 , null
							 , $tlongtext
							 )
				, "for Organisation Unit" =>
						array( gevSettings::CRS_AMD_TEP_ORGU
							 , null
							 , false
							 , null
							 , $tteporgu
	 	   					 )
	 	   		))
	 , "Booking Modalities"
	 	=> array( null, 
	 	   array( "Minimum Number of Participants" =>
	 	   				array( gevSettings::CRS_AMD_MIN_PARTICIPANTS
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Waiting List"	=>
	 	   				array( gevSettings::CRS_AMD_WAITING_LIST_ACTIVE
	 	   					 , null
	 	   					 , false
	 	   					 , array( gevSettings::YES
	 	   					 		, gevSettings::NO
	 	   					 		)
	 	   					 , $tselect
	 	   					 )
	 	   		, "Maximum Number of Participants" =>
	 	   				array( gevSettings::CRS_AMD_MAX_PARTICIPANTS
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Deadline for Cancellation" =>
	 	   				array( gevSettings::CRS_AMD_CANCEL_DEADLINE
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Buchungsfrist" =>
	 	   				array( gevSettings::CRS_AMD_BOOKING_DEADLINE
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
				 			 // if this is changed, gevUserUtils::getCourseHighlights
				 			 // needs to be changed as well!!
	 	   					 )
	 	   		, "Absage Wartelist" =>
	 	   				array( gevSettings::CRS_AMD_CANCEL_WAITING
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		))
	 , "Topic" 
		=> array( null,
		   array( "Training Category" =>
				 		array( gevSettings::CRS_AMD_TOPIC
				 			 , null
				 			 , true
				 			 , array( "Leadership | Communication (L-CO)"
									, "Leadership | Strategy (L-SY)"
									, "Leadership | Corporate Culture (L-CU)"
									, "Leadership | Appraisal Interviews (L-AI)"
									, "Leadership | Coaching and Mentoring (L-CM)"
									, "Leadership | Basic Knowledge (L-BK)"
									, "Professional Expertise | Marketing & Sales (E-MS)"
									, "Professional Expertise | Product Portfolio (E-PP)"
									, "Professional Expertise | Technology (E-TY)"
									, "Professional Expertise | Business Administration (E-BA)"
									, "Professional Expertise | Information Technology (E-IT)"
									, "Professional Expertise | Law (E-LA)"
									, "Professional Expertise | Languages (E-LG)"
									, "Professional Expertise | Protection and Safety (E-PS)"
									, "Professional Expertise | Methods & Instruments (E-MI)"
									)
				 			 , $tmultiselect
				 			 )
				, "Training Topics" =>
						array( gevSettings::CRS_AMD_CONTENTS
							 , null
							 , false
							 , null
							 , $tlongtext
							 )
				, "Objectives and Use" =>
						array( gevSettings::CRS_AMD_GOALS
							 , null
							 , false
							 , null
							 , $tlongtext 
							 )
				, "Language" =>
						array( gevSettings::CRS_AMD_LANG
							 , null
							 , true
							 , array( "DE (Deutsch)"
									, "EN (English)"
									, "ZH (中文)"
									, "ES (Español)"
									, "FR (Français)"
									, "SV (Svenska)"
									, "DA (Dansk)"
									, "NO (Norsk)"
									, "FI (Suomi)"
									, "PO (Język polski)"
									, "RU (Русский)"
									)
							 , $tselect
							 )
				))
	 , "Target Groups"
		=> array( null,
		   array( "Description of Target Group" =>
		   				array( gevSettings::CRS_AMD_TARGET_GROUP_DESC
		   					 , null
		   					 , false
		   					 , null
		   					 , $tlongtext
		   					 )
		   ))
	 , "Accounting"
	 	=> array( null,
	 	   array( "Participation Fee" =>
	 	   				array( gevSettings::CRS_AMD_FEE
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		, "Fixed Cost" =>
	 	   				array( gevSettings::CRS_AMD_FIXED_COST
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		, "Variable Cost" =>
	 	   				array( gevSettings::CRS_AMD_VARIABLE_COST
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		, "Currency" =>
	 	   				array( gevSettings::CRS_AMD_CURRENCY
	 	   					 , null
	 	   					 , false
	 	   					 , array( "EUR"
									, "USD"
									, "GBP"
									, "MYR"
									, "CNY"
									, "SEK"
									, "NOK"
									, "DKK"
									, "AUD"
									, "JPY"
									, "RUB"
	 	   					 		)
	 	   					 , $tselect
	 	   					 )
	 	   		))
	, "Administration"
		=> 	array( null, 
			array( "Training Number" => 
						array( gevSettings::CRS_AMD_CUSTOM_ID		# 0 to save in settings
							 , null  								# 1 description
							 , true 								# 2 searchable
							 , null 								# 3 definition
							 , $ttext 								# 4 type
							 // if this is changed, the custom id logic in gevCourseUtils
							 // needs to be changed as well!!
							 )
				 , "Training Type" =>
				 		array( gevSettings::CRS_AMD_TYPE
				 			 , null
				 			 , true
				 			 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
				 			 , array( "Live Training"
				 			 		, "Online Training"
				 			 		, "Webinar"
									)
				 			 // if this is changed, gevUserUtils::getCourseHighlights
				 			 // needs to be changed as well!!
				 			 , $tselect
				 			 )
				, "Educational Program" =>
						array( gevSettings::CRS_AMD_EDU_PROGRAM
							 , null
							 , true
							 , array( "High Potentials (SPX-HP)"
									, "Onboarding (SPX-OB)"
									, "Azubi-Ausbildung (SPX-AA)"
									, "Dezentrales Training "
							 	    )
							 , $tselect
							 )
				 , "Template" =>
				 		array( gevSettings::CRS_AMD_IS_TEMPLATE
				 			 , null
				 			 , false
				 			 , array ( "Yes"
				 			 		 , "No"
				 			 		 )
				 			 , $tselect
				 			 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
				 			 )
				 , "Template Title" =>
				 		array( gevSettings::CRS_AMD_TEMPLATE_TITLE
				 			 , "don't change"
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "Reference ID (template)" =>
				 		array( gevSettings::CRS_AMD_TEMPLATE_REF_ID
				 			 , "don't change"
				 			 , false
				 			 , array("min" => 0)
				 			 , $tinteger
				 			 )
				 , "Range of Numbers" =>
				 		array( gevSettings::CRS_AMD_CUSTOM_ID_TEMPLATE
							 , null
							 , false
							 , array( "SPX-HP High Potentials"
									, "SPX-OB Onboarding"
									, "SPX-AA Azubi-Ausbildung"
									, "SPX internal Training"
									, "CT Customer Training"
									, "DT Distributor Training"
									)
							 , $tselect
							 )
				 ))

	);

gevAMDUtils::createAMDRecords($records, array("crs"));
?>

<#2>
<?php

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");

$options = array
	( "High Potentials (SPX-HP)"
	, "Onboarding (SPX-OB)"
	, "Azubi-Ausbildung (SPX-AA)"
	);

$amdutils = gevAMDUtils::getInstance();
$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_EDU_PROGRAM, $options);

?>

<#3>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Topic"
						, "Reason for training"
						, gevSettings::CRS_AMD_REASON_FOR_TRAINING
						, null
						, false
						, array( "R - Rechtliche Anforderung, Pflichtschulung"
							, "D - Maßnahmen aus Defizit"
							, "PE - Personalentwicklung"
							, "O - Online Training"
							)
						, ilAdvancedMDFieldDefinition::TYPE_SELECT
						);
?>