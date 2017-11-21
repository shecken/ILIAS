<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportWBDPoints extends ilObjReportBase
{
	protected $relevant_parameters = array();

	public function initType()
	{
		$this->setType("xwbp");
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_wbp');
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_wbd_edupoints_row.html";
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function buildQuery($query)
	{
		return null;
	}

	protected function buildTable($table)
	{
		$table	->column("firstname", $this->plugin->txt("firstname"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("birthday", $this->plugin->txt("birthday"), true)
				->column("bwv_id", $this->plugin->txt("bwv_id"), true)
				->column("wbd_type", $this->plugin->txt("wbd_type"), true)
				->column("title", $this->plugin->txt("crs_title"), true)
				->column("begin_date", $this->plugin->txt("begin_date"), true)
				->column("end_date", $this->plugin->txt("end_date"), true)
				->column("credit_points", $this->plugin->txt("wb_time"), true)
				->column("wbd_booking_id", $this->plugin->txt("wbd_booking_id"), true)
				->column("custom_id", $this->plugin->txt("training_id_2"), true)
				->column("type", $this->plugin->txt("course_type"), true);
		return parent::buildTable($table);
	}

	public function filter()
	{
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function ($id) {
			return $this->plugin->txt($id);
		};

		return
		$f->sequence(
			$f->sequence(
				$f->dateperiod(
					$txt("period"),
					""
				)->map(
					function ($start, $end) use ($f) {
							$pc = $f->dateperiod_overlaps_predicate(
								"usrcrs.begin_date",
								"usrcrs.begin_date"
							);
							return array ("date_period_predicate" => $pc($start, $end)
										 ,"start" => $start
										 ,"end" => $end);
					},
					$tf->dict(
						array
							(
								"date_period_predicate" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
								,"start" => $tf->cls("DateTime")
								,"end" => $tf->cls("DateTime")
							)
					)
				),
				$f->multiselectsearch(
					$txt("filter_wbd_type"),
					"",
					$this->getDistinctValues('wbd_type', 'hist_user')
				),
				$f->text(
					$txt("lastname"),
					""
				)
			)
		)->map(
			function ($date_period_predicate, $start, $end, $wbd_types, $name) {
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"wbd_types" => $wbd_types
									,"name" => $name);
			},
			$tf->dict(
				array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							 ,"start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"wbd_types" => $tf->lst($tf->string())
							 ,"name" => $tf->string())
			)
		);
	}

	public function buildQueryStatement()
	{
		$db = $this->gIldb;
		$query =   "SELECT DISTINCT `usr`.`firstname` ,`usr`.`lastname` ,`usr`.`birthday` ,`usr`.`bwv_id` ,`usr`.`wbd_type` ,`crs`.`title`\n"
				  .", `crs`.`type` ,`usrcrs`.`begin_date` ,`usrcrs`.`end_date` ,`usrcrs`.`credit_points` ,`usrcrs`.`wbd_booking_id`\n"
				  .", IF ( crs.custom_id <> '-empty-' , crs.custom_id , IF (usrcrs.gev_id IS NULL , '-' , usrcrs.gev_id ) ) as custom_id\n"
				  ." FROM `hist_usercoursestatus` usrcrs\n"
				  ." JOIN `hist_user` usr\n"
				  ."     ON usrcrs.usr_id = usr.user_id\n"
				  ."         AND usr.hist_historic = 0\n"
				  ." JOIN `hist_course` crs\n"
				  ."     ON usrcrs.crs_id = crs.crs_id\n"
				  ."         AND crs.hist_historic = 0\n"
				  ." WHERE usrcrs.hist_historic = 0\n"
				 // ."     AND usrcrs.wbd_booking_id != '-empty-'\n"
				  ."     AND usr.hist_historic = 0\n"
				  ."     AND crs.hist_historic = 0\n"
				  ."     AND crs.type != ".$this->gIldb->quote(gevCourseUtils::CRS_TYPE_COACHING, "text")."\n"
				  ."     AND usrcrs.hist_historic = 0\n";

		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings["period_pred"]);
			$query .= "     AND ".$dt_query;

			if (!empty($settings['name'])) {
				$query .= "    AND " .$db->like('usr.lastname', 'text', $settings['name']);
			}

			if (!empty($settings['wbd_types'])) {
				$query .= "    AND " .$db->in('usr.wbd_type', $settings['wbd_types'], false, "text");
			}
		}

		$query .= $this->queryOrder();

		return $query;
	}

	protected function fetchData(callable $callback)
	{
		$db = $this->gIldb;
		$query = $this->buildQueryStatement();
		$res = $this->gIldb->query($query);
		$data = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback, $rec);
		}
		return $data;
	}

	protected function buildFilter($filter)
	{
		return null;
	}

	protected function buildOrder($order)
	{
		return $order;
	}

	public function getDistinctValues($a_field, $a_table, $a_order = 'ASC', $a_showempty = false, $a_filter_historic = false)
	{
		global $ilDB;
		$where = "WHERE TRIM($a_field) NOT IN ('-empty-', '')"
				." AND $a_field IS NOT NULL"
				;
		if ($a_showempty) {
			$where = 'WHERE 1';
		}
		if ($a_filter_historic) {
			$where .= ' AND hsit_historic=0';
		}


		$sql = "SELECT DISTINCT $a_field FROM $a_table $where ORDER BY $a_field $a_order";
		$res = $ilDB->query($sql);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[$rec[$a_field]] = $rec[$a_field];
		}

		return $ret;
	}
}
