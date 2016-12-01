<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportWBDPoints extends ilObjReportBase {
	protected $relevant_parameters = array();

	public function initType() {
		$this->setType("xwbp");
	}
	
	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_wbp');
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_wbd_edupoints_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildQuery($query) {
		// $query	->distinct()
		// 		->select("usr.firstname")
		// 		->select("usr.lastname")
		// 		->select("usr.birthday")
		// 		->select("usr.bwv_id")
		// 		->select("usr.wbd_type")
		// 		->select("crs.title")
		// 		->select_raw(" IF ( crs.custom_id <> '-empty-'"
		// 					."    , crs.custom_id "
		// 					."    , IF (usrcrs.gev_id IS NULL"
		// 					."         , '-'"
		// 					."         , usrcrs.gev_id"
		// 					."         )"
		// 					."    ) as custom_id")
		// 		->select("crs.type")
		// 		->select("usrcrs.begin_date")
		// 		->select("usrcrs.end_date")
		// 		->select("usrcrs.credit_points")
		// 		->select("usrcrs.wbd_booking_id")
		// 		->from("hist_usercoursestatus usrcrs")
		// 		->join("hist_user usr")
		// 			->on("usrcrs.usr_id = usr.user_id AND usr.hist_historic = 0")
		// 		->join("hist_course crs")
		// 			->on("usrcrs.crs_id = crs.crs_id AND crs.hist_historic = 0")
		// 		->compile();
		// return null;
	}

	protected function buildTable($table) {
		$table	->column("firstname", $this->plugin->txt("firstname"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("birthday", $this->plugin->txt("birthday"), true)
				->column("bwv_id", $this->plugin->txt("bwv_id"), true)
				->column("wbd_type", $this->plugin->txt("wbd_type"), true)
				->column("title", $this->plugin->txt("crs_title"), true)
				->column("begin_date", $this->plugin->txt("begin_date"), true)
				->column("end_date", $this->plugin->txt("end_date"), true)
				->column("credit_points", $this->plugin->txt("credit_points"), true)
				->column("wbd_booking_id", $this->plugin->txt("wbd_booking_id"), true)
				->column("custom_id", $this->plugin->txt("training_id_2"), true)
				->column("type", $this->plugin->txt("course_type"), true);
		return parent::buildTable($table);
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->plugin->txt($id); };

		return 
		$f->sequence
		(
			$f->sequence
			(
				$f->dateperiod
				(
					$txt("period")
					, ""
				)->map
					(
						function($start,$end) use ($f) 
						{
							$pc = $f->dateperiod_overlaps_predicate
							(
								"ht.begin_date"
								,"ht.begin_date"
							);
							return array ("date_period_predicate" => $pc($start,$end)
										 ,"start" => $start
										 ,"end" => $end);
						},
						$tf->dict
						(
							array
							(
								"date_period_predicate" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
								,"start" => $tf->cls("DateTime")
								,"end" => $tf->cls("DateTime")
							)
						)
					),
				$f->multiselectsearch
				(
					$txt("filter_wbd_type")
					, ""
					, catFilter::getDistinctValues('wbd_type', 'hist_user')
				)->map
					(
						function($types) { return $types; }
						,$tf->lst($tf->string())
					),
				$f->text
				(
					$txt("lastname")
				)->map
					(
						function($name) { return $name; }
						,$tf->string()
					)
			)->map
				(
					function($date_period_predicate, $start, $end, $wdb_types, $name)
					{
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"wdb_types" => $wdb_types
									,"name" => $name);
					},
					$tf->dict
					(
						array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							 ,"start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"wdb_types" => $tf->lst($tf->string())
							 ,"name" => $tf->string())
					)
				)
		);
	}

	protected function fetchData(callable $callback) {
		$db = $this->gIldb;
	    $query =   "SELECT DISTINCT `usr`.`firstname` ,`usr`.`lastname` ,`usr`.`birthday` ,`usr`.`bwv_id` ,`usr`.`wbd_type` ,`crs`.`title` 
							,`crs`.`type` ,`usrcrs`.`begin_date` ,`usrcrs`.`end_date` ,`usrcrs`.`credit_points` ,`usrcrs`.`wbd_booking_id` 
							, IF ( crs.custom_id <> '-empty-' , crs.custom_id , IF (usrcrs.gev_id IS NULL , '-' , usrcrs.gev_id ) ) as custom_id 
					FROM `hist_usercoursestatus` usrcrs 
					JOIN `hist_user` usr ON usrcrs.usr_id = usr.user_id 
						AND usr.hist_historic = 0 
					JOIN `hist_course` crs ON usrcrs.crs_id = crs.crs_id 
						AND crs.hist_historic = 0 
					WHERE TRUE 
						AND usrcrs.hist_historic = 0 
						AND usrcrs.wbd_booking_id IS NOT NULL 
						AND usr.hist_historic = 0 
						AND crs.hist_historic = 0 
						AND ( ( (`usrcrs`.`end_date` >= '2016-01-01' OR `usrcrs`.`end_date` = '0000-00-00' OR `usrcrs`.`end_date` = '-empty-' )
							AND `usrcrs`.`begin_date` <= '2016-12-31' ) OR usrcrs.hist_historic IS NULL ) 
						AND TRUE 
						AND `usr`.`lastname` LIKE '%' 
						AND TRUE";

		$filter = $this->filter();
		if($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings[0]["period_pred"]);
			$query .= " WHERE ".$dt_query;
		}

		if ($query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		$res = $this->gIldb->query($query);
		$data = array();

		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
		}

		return $data;

	}

	protected function buildFilter($filter) {
		// $filter ->dateperiod( "period"
		// 					, $this->plugin->txt("period")
		// 					, $this->plugin->txt("until")
		// 					, "usrcrs.begin_date"
		// 					, "usrcrs.end_date"
		// 					, date("Y")."-01-01"
		// 					, date("Y")."-12-31"
		// 					, false
		// 					, " OR usrcrs.hist_historic IS NULL"
		// 					)
		// 		->multiselect("wbd_type"
		// 					 , $this->plugin->txt("filter_wbd_type")
		// 					 , "wbd_type"
		// 					 , catFilter::getDistinctValues('wbd_type', 'hist_user')
		// 					 , array()
		// 					 , ""
		// 					 , 300
		// 					 , 160
		// 					 )
		// 		->textinput( "lastname"
		// 				   , $this->plugin->txt("lastname_filter")
		// 				   , "usr.lastname"
		// 				   )
		// 		->static_condition(" usrcrs.hist_historic = 0")
		// 		->static_condition(" usrcrs.wbd_booking_id IS NOT NULL")
		// 		->static_condition(" usr.hist_historic = 0")
		// 		->static_condition(" crs.hist_historic = 0")
		// 		->action($this->filter_action)
		// 		->compile();
		// return null;
	}

	public function deliverSumTable() {
		return $this->table_sums;
	}

	public function fetchSumData() {
		return $this->sum_row;
	}

	protected function buildOrder($order) {
		return $order;
	}
}