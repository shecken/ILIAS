<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class gevSettings
*
* Get and set settings for the generali. Wrapper around ilSettings.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*/

require_once("Services/Administration/classes/class.ilSetting.php");

class gevSettings {
	static $instance = null;
	static $amd_fields = null;
	
	const MODULE_NAME = "gev";
	
	# For queries
	const LIVE_TRAINING = "Live Training";
	const WEBINAR = "Webinar";
	const ONLINE_TRAINING = "Online Training";
	
	const YES = "Yes";
	const NO = "No";
	
	// vgl. Konzept, Abschnitt Trainingsvorlagen
	
	// Block "Trainingsverwaltung"
	// Nummer der Maßnahme
	const CRS_AMD_CUSTOM_ID 		= "crs_amd_custom_id";
	// Nummernkreis
	const CRS_AMD_CUSTOM_ID_TEMPLATE = "crs_amd_custom_id_template";
	// Trainingsvorlage (nicht im Konzept)
	const CRS_AMD_TEMPLATE_TITLE	= "crs_amd_template_title";
	// Trainingsvorlage Ref-ID (nicht im Konzept)
	const CRS_AMD_TEMPLATE_REF_ID	= "crs_amd_template_ref_id";
	//Lernart
	const CRS_AMD_TYPE 				= "crs_amd_type";
	// Vorlage
	const CRS_AMD_IS_TEMPLATE		= "crs_amd_is_template";
	
	// Trainingsbetreuer -> ILIAS Standard
	//const CRS_AMD_MAINTAINER		= "crs_amd_maintainer";

	// Start- und Enddatum
	const CRS_AMD_START_DATE		= "crs_amd_start_date";
	const CRS_AMD_END_DATE			= "crs_amd_end_date";
	// Zeitplan
	const CRS_AMD_SCHEDULE			= "crs_amd_schedule";
	// geplant für
	const CRS_AMD_SCHEDULED_FOR		= "crs_amd_scheduled_for";
	// Organisatorisches
	const CRS_AMD_ORGA				= "crs_amd_orga";
	
	// Block Trainingsinhalte
	// Trainingsthema
	const CRS_AMD_TOPIC 			= "crs_amd_topic";
	// Inhalte
	const CRS_AMD_CONTENTS 			= "crs_amd_content";
	// Ziele und Nutzen
	const CRS_AMD_GOALS 			= "crs_amd_goals";
	// Methoden
	const CRS_AMD_METHODS 			= "crs_amd_methods";
	// Medien
	const CRS_AMD_MEDIA				= "crs_amd_media";
	// Bildungsprogramm
	const CRS_AMD_EDU_PROGRAM		= "crs_amd_edu_program";
	// Sprache
	const CRS_AMD_LANG				= "crs_amd_lang";
	// Wirksamkeitsanalyse
	const CRS_AMD_REASON_FOR_TRAINING = "crs_amd_reason_for_training";

	// Zielgruppenbeschreibung
	const CRS_AMD_TARGET_GROUP_DESC	= "crs_amd_target_group_desc";

	
	// Teilnahmegebühr
	const CRS_AMD_FEE				= "crs_amd_fee";
	// Fixe Kosten
	const CRS_AMD_FIXED_COST		= "crs_amd_fixed_cost";
	// Variable Kosten
	const CRS_AMD_VARIABLE_COST		= "crs_amd_variable_cost";
	
	// Währung
	const CRS_AMD_CURRENCY			= "crs_amd_currency";
	
	// Mindestteilnehmerzahl
	const CRS_AMD_MIN_PARTICIPANTS	= "crs_amd_min_participants";
	// Warteliste
	const CRS_AMD_WAITING_LIST_ACTIVE = "crs_amd_waiting_list_active";
	// Maximalteilnehmerzahl
	const CRS_AMD_MAX_PARTICIPANTS	= "crs_amd_max_participants";
	// Stornofrist
	const CRS_AMD_CANCEL_DEADLINE	= "crs_amd_cancel_deadline";
	// Buchungsfrist
	const CRS_AMD_BOOKING_DEADLINE	= "crs_amd_booking_deadline";
	// Absage Wartliste
	const CRS_AMD_CANCEL_WAITING	= "crs_amd_cancel_waiting";
	
	// Anbieter
	const CRS_AMD_PROVIDER			= "crs_amd_provider";
	// Veranstaltungsort
	const CRS_AMD_VENUE				= "crs_amd_venue";
	// Übernachtungsort
	const CRS_AMD_ACCOMODATION		= "crs_amd_accomodation";
	// Veranstaltungsort Internet
	//const CRS_AMD_WEB_LOCATION		= "crs_amd_web_location";
	const CRS_AMD_WEBINAR_LINK		= "crs_amd_webinar_link";
	const CRS_AMD_WEBINAR_PASSWORD	= "crs_amd_webinar_password";
	
	// Organisationseinheit TEP
	const CRS_AMD_TEP_ORGU			= "crs_amd_tep_orgu";

	// Crs User PState
	const CRS_URS_STATE_SUCCESS			= "erfolgreich";
	const CRS_URS_STATE_SUCCESS_VAL		= "2";
	const CRS_URS_STATE_EXCUSED			= "entschuldigt";
	const CRS_URS_STATE_EXCUSED_VAL		= "3";
	const CRS_URS_STATE_NOT_EXCUSED		= "unentschuldigt";
	const CRS_URS_STATE_NOT_EXCUSED_VAL	= "4";

	//Crs User LPState
	const CRS_LP_STATE_SUCCESS 			= "2";
	const CRS_LP_STATE_NOT_STARTED		= "0";
	const CRS_LP_U_COMMENT_SUCCESS		= "lp_status_2";
	const CRS_LP_U_COMMENZT_NOT_STARTED	= "lp_status_0";
	const CRS_LP_COMPLETED_SUCCESS		= "1";
	const CRS_LP_COMPLETED_NOT_STARTED	= "0";
	
	
	// Typen von Organisationseinheiten
	const ORG_TYPE_VENUE			= "org_unit_type_venue";
	const ORG_TYPE_PROVIDER			= "org_unit_type_provider";
	const ORG_TYPE_DEFAULT			= "org_unit_type_default";
	
	static $all_org_types = array( gevSettings::ORG_TYPE_VENUE
								 , gevSettings::ORG_TYPE_PROVIDER
								 , gevSettings::ORG_TYPE_DEFAULT
								 );
	
	// AMD für alle Org-Units (vgl. Konzept, Abschnitte Veranstaltungsorte, Anbieter)
	// Straße
	const ORG_AMD_STREET			= "org_amd_street";
	// Hausnummer
	const ORG_AMD_HOUSE_NUMBER		= "org_amd_house_number";
	// Postleitzahl
	const ORG_AMD_ZIPCODE			= "org_amd_zipcode";
	// Ort
	const ORG_AMD_CITY				= "org_amd_city";
	// Ansprechpartner
	const ORG_AMD_CONTACT_NAME		= "org_amd_contact_name";
	// Telefon
	const ORG_AMD_CONTACT_PHONE		= "org_amd_contact_phone";
	// Fax
	const ORG_AMD_CONTACT_FAX		= "org_amd_contact_fax";
	// eMail
	const ORG_AMD_CONTACT_EMAIL		= "org_amd_contact_email";
	// Homepage
	const ORG_AMD_HOMEPAGE			= "org_amd_homepage";

	// Kostenstelle
	const ORG_AMD_FINANCIAL_ACCOUNT	= "org_amd_financial_account";


	// AMD für Veranstaltungsorte
	// Anfahrt
	const VENUE_AMD_LOCATION		= "venue_amd_location";
	// Kosten je Übernachtung
	const VENUE_AMD_COSTS_PER_ACCOM	= "venue_amd_costs_per_accom";
	// Pauschale Frühstück
	const VENUE_AMD_COSTS_BREAKFAST	= "venue_amd_costs_breakfast";
	// Pauschale Mittagessen
	const VENUE_AMD_COSTS_LUNCH		= "venue_amd_costs_lunch";
	// Nachmittagspauschale
	const VENUE_AMD_COSTS_COFFEE	= "venue_amd_costs_coffee";
	// Pauschale Abendessen
	const VENUE_AMD_COSTS_DINNER	= "venue_amd_costs_dinner";
	// Pauschale Tagesverpflegung
	const VENUE_AMD_COSTS_FOOD		= "venue_amd_costs_food";
	// Vollkostenpauschale Hotel
	const VENUE_AMD_COSTS_HOTEL		= "venue_amd_costs_hotel";
	// Tagespauschale Hotel
	const VENUE_AMD_ALL_INCLUSIVE_COSTS = "venue_amd_all_inclusive_costs";
	
	
	// Standardorte und -veranstalter
	const VENUE_BERNRIED			= "venue_bernried";
	const PROVIDER_GENERALI			= "provider_generali";
	
	const USR_UDF_COMPANY_NAME 		= "usr_udf_company_name";
	const USR_UDF_SALES_REGION 		= "usr_udf_sales_region";
	const USR_UDF_ENTRY_DATE 		= "usr_udf_entry_date";
	const USR_UDF_EXIT_DATE 		= "usr_udf_exit_date";
	
	static $UDF_FIELD_ORDER = array(
		
	);

	// Names of roles that count as admins
	static $ADMIN_ROLES = array(
		  "Administrator"
		, "Admin-eingeschraenkt"
		, "Admin-Voll"
		);
	
	// Names of roles that count as superiors
	static $SUPERIOR_ROLES = array(
		  "il_orgu_superior_%"
		, "DBV"
		);

	// Names of roles that count as tutors
	static $TUTOR_ROLES = array(
		  "il_crs_tutor_%"
		);
	
	// Names of the functional roles that could be assigned to a user in
	// the user administration
	static $FUNCTIONAL_ROLES = array
		( "Distributor"
		, "Customer"
		);
	
	// Names of the country roles
	static $COUNTRY_ROLES = array
		( "DE"
		, "US"
		, "FR"
		, "UK"
		, "MY"
		, "CN"
		, "Nordic"
		, "JP"
		, "AUS"
		, "RU"
		, "IN"
		, "IT"
		);
	
	// Will store the ref id of the orgu where the exited users should be put.
	const ORG_UNIT_EXITED = "org_unit_exited";
	
	public function getOrgUnitExited() {
		return $this->settings->get(self::ORG_UNIT_EXITED);
	}
	
	public function setOrgUnitExited($a_ref_id) {
		return $this->settings->set(self::ORG_UNIT_EXITED, $a_ref_id);
	}
	
	static $TEPTYPE_ORDER = array(
		'Training',
		
		'Projekt',
		'Veranstaltung / Tagung (Zentral)',
		'Trainer- / DBV Klausur (Zentral)',
		'Trainer Teammeeting',
		'Arbeitsgespräch',
		
		'AD-Begleitung',
		'Firmenkunden',
		//'Aquise Pilotprojekt',
		'Akquise Pilotprojekt',
		'Individuelle Unterstützung SpV/FD',
		'Büro',
		
		'Urlaub beantragt',
		'Dezentraler Feiertag',
		'Urlaub genehmigt',
		'Ausgleichstag',
		'Krankheit',
		
		'OD-FD Meeting',
		'FD-Gespräch',
		'OD-Gespräch',
		'AKL-Gespräch',
		'FD-MA Teammeeting',
		
		'Gewerbe-Arbeitskreis',
		'bAV-Arbeitskreis',
		'FDL-Arbeitskreis'
	);
	
	private function __construct() {
		$this->settings = new ilSetting(self::MODULE_NAME);
	}
	
	public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevSettings();
		}
		
		return self::$instance;
	}
	
	public function get($a_field) {
		return $this->settings->get($a_field);
	}
	
	public function set($a_field, $a_value) {
		$this->settings->set($a_field, $a_value);
	}
	
	public function getAMDFieldId($a_field) {
		$field_id = explode(" ", $this->get($a_field));
		return $field_id[1];
	}
	
	public function getUDFFieldId($a_field) {
		return $this->get($a_field);
	}
}

?>
