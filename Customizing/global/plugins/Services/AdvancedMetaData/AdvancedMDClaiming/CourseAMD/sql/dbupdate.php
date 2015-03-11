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
array( "Zeitraum"
	 	=> array(null,
	 	   array( "Startdatum" =>	
	 	   				array( gevSettings::CRS_AMD_START_DATE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tdate
	 	   					 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
	 	   					 )
	 	   		, "Enddatum" =>
	 	   				array( gevSettings::CRS_AMD_END_DATE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tdate
	 	   					 )
	 	   		, "Zeitplan" =>
	 	   				array( gevSettings::CRS_AMD_SCHEDULE
	 	   					 , null
	 	   					 , false
	 	   					 , null
	 	   					 , $tschedule
	 	   					 )
				, "geplant für" =>
						array( gevSettings::CRS_AMD_SCHEDULED_FOR
							 , ""
							 , false
							 , null
							 , $ttext
							 )
	 	   		))
	 , "Orte und Anbieter"
	 	=> array( null, 
	 	   array( "Anbieter" =>
	 	   				array( gevSettings::CRS_AMD_PROVIDER
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tprovider
	 	   					 )
	 	   		, "Veranstaltungsort" =>
	 	   				array( gevSettings::CRS_AMD_VENUE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tvenue
	 	   					 )
	 	   		, "Übernachtungsort" =>
	 	   				array( gevSettings::CRS_AMD_ACCOMODATION
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tvenue
	 	   					 )
				, "Webinar Link" =>
						array( gevSettings::CRS_AMD_WEBEX_LINK
							 , "Link zum virtuellen Klassenraum"
							 , false
							 , null
							 , $ttext
							 )
				, "Webinar Passwort" =>
						array( gevSettings::CRS_AMD_WEBEX_PASSWORD
							 , "Passwort zum virtuellen Klassenraum"
							 , false
							 , null
							 , $ttext
	 	   					 )
				, "Organisatorisches" =>
						array( gevSettings::CRS_AMD_ORGA
							 , ""
							 , false
							 , null
							 , $tlongtext
							 )
				, "für Organisationseinheit" =>
						array( gevSettings::CRS_AMD_TEP_ORGU
							 , ""
							 , false
							 , null
							 , $tteporgu
	 	   					 )
	 	   		))
	 , "Buchungsmodalitäten"
	 	=> array( "Fristen und Teilnehmerzahlen", 
	 	   array( "Mindestteilnehmerzahl" =>
	 	   				array( gevSettings::CRS_AMD_MIN_PARTICIPANTS
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Warteliste"	=>
	 	   				array( gevSettings::CRS_AMD_WAITING_LIST_ACTIVE
	 	   					 , null
	 	   					 , false
	 	   					 , array( "Ja"
	 	   					 		, "Nein"
	 	   					 		)
	 	   					 , $tselect
	 	   					 )
	 	   		, "Maximalteilnehmerzahl" =>
	 	   				array( gevSettings::CRS_AMD_MAX_PARTICIPANTS
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Stornofrist" =>
	 	   				array( gevSettings::CRS_AMD_CANCEL_DEADLINE
	 	   					 , "Tage vor dem Seminar, bis zu denen noch kostenfrei storniert werden kann."
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
				, "harte Stornofrist" =>
						array( gevSettings::CRS_AMD_ABSOLUTE_CANCEL_DEADLINE
							 , "Tage vor dem Seminar, bis zu denen noch storniert werden kann."
							 , false
							 , array("min" => 0)
							 , $tinteger
							 )
	 	   		, "Buchungsfrist" =>
	 	   				array( gevSettings::CRS_AMD_BOOKING_DEADLINE
	 	   					 , "Tage vor dem Seminar, bis zu denen das Seminar gebucht werden kann."
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
				 			 // if this is changed, gevUserUtils::getCourseHighlights
				 			 // needs to be changed as well!!
	 	   					 )
	 	   		, "Absage Wartelist" =>
	 	   				array( gevSettings::CRS_AMD_CANCEL_WAITING
	 	   					 , "Tag vor dem Seminar, an dem die Warteliste abgesagt wird."
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		))
	 , "Inhalte" 
		=> array( "Inhalte und Medien des Trainings",
		   array( "Trainingskategorie" =>
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
				, "Trainingsinhalte" =>
						array( gevSettings::CRS_AMD_CONTENTS
							 , "Beschreibung der Trainingsinhalte"
							 , false
							 , null
							 , $tlongtext
							 )
				, "Bildungsprogramm" =>
						array( gevSettings::CRS_AMD_EDU_PROGRAMM
							 , null
							 , true
							 , array( "High Potentials (SPX-HP)"
									, "Onboarding (SPX-OB)"
									, "Azubi-Ausbildung (SPX-AA)"
									, "Dezentrales Training "
							 	    )
							 , $tselect
							 )
				, "Ziele und Nutzen" =>
						array( gevSettings::CRS_AMD_GOALS
							 , "Beschreibung des Nutzens der Teilnehmer"
							 , false
							 , null
							 , $tlongtext 
							 )
				, "Methoden" =>
						array( gevSettings::CRS_AMD_METHODS
							 , "Beim Training eingesetzte Methoden"
							 , true
							 , array( "Vortrag"
							 		, "Gruppenarbeit"
							 		, "Partnerarbeit"
							 		, "Einzelarbeit"
							 		, "Diskussion"
							 		, "Brainstorming"
							 		, "Rollenspiele"
							 		)
							 , $tmultiselect
							 )
				, "Medien" =>
						array( gevSettings::CRS_AMD_MEDIA
							 , "Beim Training eingesetzte Medien"
							 , true
							 , array( "PowerPoint"
							 		, "Flipchart"
							 		, "Metakarten"
							 		, "myGenerali"
							 		, "Spezialsoftware"
							 		, "Arbeitsblatt / Handout"
							 		, "Film"
							 		, "Internet / Intranet"
							 		)
							 , $tmultiselect
							 )
				, "Sprache" =>
						array( gevSettings::CRS_AMD_LANG
							 , ""
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
	 , "Zielgruppen"
		=> array( "Zielgruppen des Trainings",
		   array( "Zielgruppenbeschreibung" =>
		   				array( gevSettings::CRS_AMD_TARGET_GROUP_DESC
		   					 , "Beschreibung der Zielgruppe des Trainings"
		   					 , false
		   					 , null
		   					 , $tlongtext
		   					 )
		   ))
	 , "Abrechnung"
	 	=> array( null,
	 	   array( "Teilnahmegebühr" =>
	 	   				array( gevSettings::CRS_AMD_FEE
	 	   					 , ""
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		, "Fixe Kosten" =>
	 	   				array( gevSettings::CRS_AMD_FIXED_COST
	 	   					 , ""
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		, "Variable Kosten" =>
	 	   				array( gevSettings::CRS_AMD_VARIABLE_COST
	 	   					 , ""
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		, "Währung" =>
	 	   				array( gevSettings::CRS_AMD_CURRENCY
	 	   					 , ""
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
	, "Verwaltung"
		=> 	array( "Einstellungen zur Verwaltung der Trainings", 
			array( "Trainingsnummer" => 
						array( gevSettings::CRS_AMD_CUSTOM_ID		# 0 to save in settings
							 , "Trainingsnummer oder Nummernkreis"  # 1 description
							 , true 								# 2 searchable
							 , null 								# 3 definition
							 , $ttext 								# 4 type
							 // if this is changed, the custom id logic in gevCourseUtils
							 // needs to be changed as well!!
							 )
				 , "Trainingstyp" =>
				 		array( gevSettings::CRS_AMD_TYPE
				 			 , "Typ des Trainings"
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
				 , "Vorlage" =>
				 		array( gevSettings::CRS_AMD_IS_TEMPLATE
				 			 , "Ist dieses Objekt ein Vorlagenobjekt?"
				 			 , false
				 			 , array ( "Ja"
				 			 		 , "Nein"
				 			 		 )
				 			 , $tselect
				 			 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
				 			 )
				 , "Vorlagentitel" =>
				 		array( gevSettings::CRS_AMD_TEMPLATE_TITLE
				 			 , "Name der verwendeten Vorlage (nicht ändern)"
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "Referenz-Id der Vorlage" =>
				 		array( gevSettings::CRS_AMD_TEMPLATE_REF_ID
				 			 , "ILIAS-Referenz-Id der verwendeten Vorlage (nicht ändern)"
				 			 , false
				 			 , array("min" => 0)
				 			 , $tinteger
				 			 )
				 , "Nummernkreis" =>
				 		array( gevSettings::CRS_AMD_CUSTOM_ID_TEMPLATE
							 , "Zu verwendender Nummernkreis für diese Vorlage"
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
