<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "AttendanceByEmployees"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*	Define title, table_cols and row_template.
*	Implement fetchData to retrieve the data you want
*
*	Add special _process_xls_XXX and _process_table_XXX methods
*	to modify certain entries after retrieving data.
*	Those methods must return a proper string.
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevAttendanceByEmployeeGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = catTitleGUI::create()
						->title("gev_rep_attendance_by_employee_title")
						->subTitle("gev_rep_attendance_by_employee_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("email", "email")
						->column("org_unit", "gev_org_unit_short")
						->column("custom_id", "gev_training_id")
						->column("title", "title")
						->column("venue", "gev_location")
						->column("type", "gev_learning_type")
						->column("date", "date")
						->column("booking_status", "gev_booking_status")
						->column("participation_status", "gev_participation_status")
						->template("tpl.gev_attendance_by_employee_row.html", "Services/GEV/Reports")
						;
		
		$this->order = catReportOrder::create($this->table)
						->mapping("date", "crs.begin_date")
						->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("lastname", "ASC")
						;
		
		$this->query = catReportQuery::create()
						->select("usr.user_id")
						->select("usr.lastname")
						->select("usr.firstname")
						->select("usr.email")
						->select_raw("GROUP_CONCAT(DISTINCT huo.orgu_title SEPARATOR ', ' ) as org_unit")
						->select("crs.custom_id")
						->select("crs.title")
						->select("crs.venue")
						->select("crs.type")
						->select("usrcrs.booking_status")
						->select("usrcrs.participation_status")
						->select("crs.begin_date")
						->select_raw("IF(crs.type = 'Online Training' AND usrcrs.participation_status = 'status_successful', usrcrs.end_date, crs.end_date) as end_date")
						->left_join("hist_userorgu huo")
							->on(" 	huo.usr_id = usr.user_id "
								."	AND huo.hist_historic = 0"
								."	AND huo.action >= 0")
						->from("hist_user usr")
						->left_join("hist_usercoursestatus usrcrs")
							->on("usr.user_id = usrcrs.usr_id AND usrcrs.hist_historic = 0")
						->left_join("hist_course crs")
							->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
						->group_by("usr.user_id")
						->group_by("crs.crs_id")
						->compile()
						;

		$this->allowed_user_ids = $this->user_utils->getEmployees();
		$this->filter = catFilter::create()
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, " OR usrcrs.hist_historic IS NULL"
									)
						/*->multiselect( "org_unit"
									 , $this->lng->txt("gev_org_unit_short")
									 , array("usr.org_unit", "org_unit_above1", "org_unit_above2")
									 , $this->user_utils->getOrgUnitNamesWhereUserIsSuperior()
									 , array()
									 )
						->multiselect("edu_program"
									 , $this->lng->txt("gev_edu_program")
									 , "edu_program"
									 , gevCourseUtils::getEduProgramsFromHisto()
									 , array()
									 )
						->multiselect("type"
									 , $this->lng->txt("gev_course_type")
									 , "type"
									 , gevCourseUtils::getLearningTypesFromHisto()
									 , array()
									 )
						->multiselect("template_title"
									 , $this->lng->txt("crs_title")
									 , "template_title"
									 , gevCourseUtils::getTemplateTitleFromHisto()
									 , array()
									 )
						->multiselect("participation_status"
									 , $this->lng->txt("gev_participation_status")
									 , "participation_status"
									 , gevCourseUtils::getParticipationStatusFromHisto()
									 , array()
									 )
						->multiselect("position_key"
									 , $this->lng->txt("gev_position_key")
									 , "position_key"
									 , gevUserUtils::getPositionKeysFromHisto()
									 , array()
									 )*/
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition(" usr.hist_historic = 0")
						->static_condition("(   usrcrs.hist_historic = 0"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("( usrcrs.booking_status != '-empty-'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.participation_status != '-empty-'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.booking_status != 'status_cancelled_without_costs'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.function NOT IN ('crs_admin', 'crs_tutor')"
										  ." OR usrcrs.hist_historic IS NULL )" )
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;

	}

	
	protected function transformResultRow($rec) {
		//date
		if ( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = ilDatePresentation::formatPeriod($start,$end);
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} 
		else {
			$date = '-';
		}
		
		$rec["date"] = $date;
		$rec["booking_status"] = $this->lng->txt($rec["booking_status"]);
		$rec["participation_status"] = $this->lng->txt($rec["participation_status"]);
		return $rec;
	}
	
	protected function transformResultHTML($rec) {
		$rec['date'] = "<nobr>".$rec["date"]."</nobr>";
		return $rec;
	}
}