<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

/**
* Report "Effectiveness Analysis"
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
*
*/
class gevEffectivenessAnalysisReportGUI extends catBasicReportGUI {
public function __construct() {
		
		parent::__construct();

		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
		$this->eff_analysis = gevEffectivenessAnalysis::getInstance();

		$this->title = catTitleGUI::create()
						->title("gev_eff_analysis_report_title")
						->subTitle("gev_eff_analysis_report_description")
						->image("GEV_img/ico-head-rep-billing.png")
						;

		$this->table = catReportTable::create()
						->column("training_number", "gev_eff_analysis_id")
						->column("title", "gev_eff_analysis_title")
						->column("type", "gev_eff_analysis_training_type")
						->column("reason_for_training", "gev_eff_analysis_reason_for")
						->column("venue", "gev_eff_analysis_city")
						->column("date", "gev_eff_analysis_date")
						->column("language", "gev_eff_analysis_lang")
						->column("superior", "gev_eff_analysis_superior")
						->column("member", "gev_eff_analysis_member")
						->column("orgunit", "gev_eff_analysis_org_unit")
						->column("scheduled", "gev_eff_analysis_scheduled")
						->column("finish_date", "gev_eff_analysis_finished_at")
						->column("result", "gev_eff_analysis_result")
						->column("link", "", false, "", true)
						->template("tpl.gev_eff_analysis.html", "Services/GEV/Reports")
						;

		$this->filter = $this->eff_analysis->getReportFilter();
		$this->filter->action($this->ctrl->getLinkTarget($this, "view"))
					 ->compile();
	}

	protected function fetchData($xls = false) {
		$filter_values = $this->eff_analysis->buildFilterValuesFromFilter($this->filter);

		$order = "";
		$order_direction = "";

		if(isset($_GET['_table_nav'])){
			$this->external_sorting = true;
			$table_nav_cmd = explode(':', $_GET['_table_nav']);

			$order = $table_nav_cmd[0];

			if ($table_nav_cmd[1] == "asc") {
				$order_direction = "ASC";
			}
			else {
				$order_direction = "DESC";
			}
		} else {
			$order = "title";
			$order_direction = "ASC";
		}

		$data = $this->eff_analysis->getEffectivenessAnalysisReportData($this->user->getId(), $filter_values, $order, $order_direction);

		foreach ($data as $key => $rec) {
			if($xls) {
				$data[$key] = $this->transformResultXLS($rec);
			} else {
				$data[$key] = $this->transformResultRow($rec);
			}
		}

		return $data;
	}

	protected function transformResultRow($rec) {
		$start = $rec["begin_date"];
		$end = $rec["end_date"];

		if($start != $end) {
			$rec["date"] = date("d.m.Y", strtotime($start))." - ".date("d.m.Y", strtotime($end));
		} else {
			$rec["date"] = date("d.m.Y", strtotime($start));
		}

		$rec["scheduled"] = date("d.m.Y", strtotime($rec["scheduled"]));

		$rec["result"] = $this->eff_analysis->getResultText($rec["result"]);

		return $rec;
	}

	protected function transformResultXLS($rec) {
		$start = $rec["begin_date"];
		$end = $rec["end_date"];

		if($start != $end) {
			$rec["date"] = date("d.m.Y", strtotime($start))." - ".date("d.m.Y", strtotime($end));
		} else {
			$rec["date"] = date("d.m.Y", strtotime($start));
		}

		$rec["scheduled"] = date("d.m.Y", strtotime($rec["scheduled"]));

		return $rec;
	}
}