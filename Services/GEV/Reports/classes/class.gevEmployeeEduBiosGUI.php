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
								." AND usrcrs.participation_status = 'status_successful'"
								." AND usrcrs.booking_status = 'status_booked'"
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
	
	protected function transformResultHTML($rec) {
		$rec["edu_bio_link"] = gevUserUtils::getEduBioLinkFor($rec["user_id"]);
		return $rec;
	}

	protected function transformResultXLS($rec) {
		$rec["edu_bio_link"] = "";
		return $rec;
	}
	
}

?>