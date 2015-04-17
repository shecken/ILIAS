<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "Employee Edu Biographies" for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevEmployeeEduBiosGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = catTitleGUI::create()
						->title("gev_rep_employee_edu_bios_title")
						->subTitle("gev_rep_employee_edu_bios_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("login", "login")
						->column("org_unit", "gev_org_unit_short")
						->template("tpl.gev_employee_edu_bios_row.html", "Services/GEV/Reports")
						;
		
		$this->order = catReportOrder::create($this->table)
						->defaultOrder("lastname", "ASC")
						;
		
		$this->query = catReportQuery::create()
						->distinct()
						->select("usr.user_id")
						->select("usr.lastname")
						->select("usr.firstname")
						->select("usrd.login")
						->select("usr.org_unit")
						->from("hist_user usr")
						->join("usr_data usrd")
							->on(" usr.user_id = usrd.usr_id")
						->left_join("hist_usercoursestatus usrcrs")
							->on("     usr.user_id = usrcrs.usr_id"
								." AND usrcrs.hist_historic = 0 "
								." AND usrcrs.credit_points > 0"
								." AND usrcrs.participation_status = 'teilgenommen'"
								." AND usrcrs.booking_status = 'gebucht'"
								." AND usrcrs.okz <> '-empty-'"
								)
						->group_by("user_id")
						->compile()
						;
		
		$this->allowed_user_ids = $this->user_utils->getEmployees();
		$ous = $this->user_utils->getOrgUnitNamesWhereUserIsSuperior();
		sort($ous);
		$this->filter = catFilter::create()
						->textinput( "lastname"
								   , $this->lng->txt("gev_lastname_filter")
								   , "usr.lastname"
								   )
						->multiselect("org_unit"
									 , $this->lng->txt("gev_org_unit")
									 , array("usr.org_unit", "usr.org_unit_above1", "usr.org_unit_above2")
									 , $ous
									 , array()
									 )
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition(" usr.hist_historic = 0")
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
	}
	
	protected function shouldExportExcel() {
		return false;
	}
	
	protected function transformResultRow($rec) {
		// credit_points
/*		if ($rec["credit_points"] == -1) {
			$rec["credit_points"] = $this->lng->txt("gev_table_no_entry");
		}
*/		
		//date
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} else {
			$date = '-';
		}
		if ($rec['cert_period'] != "-") {
			$rec['cert_period'] = ilDatePresentation::formatDate(new ilDate($rec['cert_period'], IL_CAL_DATE));
		}
		
		$rec["edu_bio_link"] = gevUserUtils::getEduBioLinkFor($rec["user_id"]);
		
		return $this->replaceEmpty($rec);
	}
	
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
}

?>