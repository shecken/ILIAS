<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrainerOpTepCat extends ilObjReportBase {
	const MIN_ROW = "3991";
	protected $categories;
	protected $relevant_parameters = array();

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		require_once $this->plugin->getDirectory().'/config/cfg.trainer_op_tep_cat.php';
	}

	public function initType() {
		 $this->setType("xttc");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rttc');
	}

	protected function buildOrder($order) {
		return $order->defaultOrder("fullname", "ASC");
	}
	
	protected function buildTable($table) {
		$table->column("fullname", $this->plugin->txt("name"), true);
		foreach($this->categories as $key => $category) {
			$table	->column('cat_'.$key, $category, true)
					->column('cat_'.$key.'_h', 'Std.', true);
		}
		return parent::buildTable($table);
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildQuery($query) {
		return null;
	}

	public function buildQueryStatement() {
		$db = $this->gIldb;
		$select = " SELECT `hu`.`user_id`\n"
				 ." ,CONCAT(hu.lastname, ', ', hu.firstname) as fullname\n";
		$from = " FROM `hist_tep` ht\n";

		foreach($this->categories as $key => $category) {
			$select .= $this->daysPerTEPCategory($category, 'cat_'.$key) ."\n";
			$select .= $this->hoursPerTEPCategory($category, 'cat_'.$key.'_h') ."\n";
		}

		$join  = "JOIN hist_user AS hu\n"
				."    ON ht.user_id = hu.user_id\n"
				."JOIN hist_tep_individ_days AS htid\n"
				."    ON individual_days = id\n"
				."JOIN hist_course AS hc\n"
				."    ON context_id = crs_id AND ht.category  = 'Training'\n";
		$where = " WHERE TRUE\n"
				."     AND (hc.hist_historic = 0 OR hc.hist_historic IS NULL)\n"
				."     AND ht.hist_historic = 0\n"
				."     AND ht.deleted = 0\n"
				."     AND hu.hist_historic = 0\n"
				."     AND (ht.category != 'Training' OR (ht.context_id != 0 AND ht.context_id IS NOT NULL))\n"
				."     AND " .$db->in('ht.category',$this->categories,false,'text') . "\n"
				."     AND ht.row_id > " .self::MIN_ROW ."\n";

		$filter = $this->filter();
		if($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings[0]['period_pred']);
			$where .= "    AND " .$dt_query ."\n";

			if(!empty($settings[0]['edu_program'])) {
				$where .= "    AND " .$db->in('hc.edu_program', $settings[0]['edu_program'], false, 'text') ."\n";
			}

			if(!empty($settings[0]['crs_title'])) {
				$where .= "    AND " .$db->in('hc.title', $settings[0]['crs_title'], false, 'text') ."\n";
			}

			if(!empty($settings[0]['course_type'])) {
				$where .= "    AND " .$db->in('hc.type', $settings[0]['course_type'], false, 'text') ."\n";
			}

			if(!empty($settings[0]['orgu_id'])) {
				$where .= "    AND " .$db->in('ht.orgu_id', $settings[0]['orgu_id'], false, 'text') ."\n";
			}

			if(!empty($settings[0]['venue'])) {
				$where .= "    AND " .$db->in('hc.venue', $settings[0]['venue'], false, 'text') ."\n";
			}
		}

		$group = " GROUP BY hu.user_id\n";
		$orderby = " ORDER BY fullname\n";
		$orderby = $this->queryOrder();

		$query = $select . $from . $join . $where . $group .$having .$orderby;
		return $query;
	}

	protected function fetchData(callable $callback) {
		$db = $this->gIldb;

		$query = $this->buildQueryStatement();
		$res = $db->query($query);
		$data = [];

		while($rec = $db->fetchAssoc($res)) {
			$data[] = call_user_func($callback, $rec);
		}
		return $data;
	}

	protected function daysPerTEPCategory($category,$name) {
		$sql = ",SUM(IF(category = "
				.$this->gIldb->quote($category,"text")." ,1,0)) AS ".$name . "\n";
		return $sql;
	}

	protected function hoursPerTEPCategory($category, $name) {
		$sql = 
		",SUM(IF(category = ".$this->gIldb->quote($category,"text")." ,\n"
		."		IF(htid.end_time IS NOT NULL AND htid.start_time IS NOT NULL,\n"
		."			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( end_time, start_time ) )* weight /720000) *2,8),\n"
		."			LEAST(CEIL( 28800* htid.weight /720000) *2,8)\n"
		."		)\n"
		."	,0)) AS ".$name . "\n";
		return $sql;
	}

	protected function buildFilter($filter) {
		return null;
	}

	public function filter() {
		$db = $this->gIldb;
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->plugin->txt($id); };

		return
		$f->sequence
		(
			$f->sequence
			(

				/* BEGIN BLOCK - PERIOD */
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
								"hc.begin_date"
								,"hc.begin_date"
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
					/* END BLOCK - PERIOD */


				/* BEGIN BLOCK - EDU PROGRAM */
				$f->multiselectsearch
				(
					$txt("edu_program")
					, ""
					, $this->changeArrKeys(gevCourseUtils::getEduProgramsFromHisto())
				),
				/* END BLOCK - EDU PROGRAM */


				/* BEGIN BLOCK - TEMPLATE TITLE */
				$f->multiselectsearch
				(
					$txt("crs_title")
					, ""
					, $this->changeArrKeys(gevCourseUtils::getTemplateTitleFromHisto())
				),
				/* END BLOCK - TEMPLATE TITLE */


				/* BEGIN BLOCK - TYPE */
				$f->multiselectsearch
				(
					$txt("course_type")
					, ""
					, $this->changeArrKeys(gevCourseUtils::getLearningTypesFromHisto())
				),
				/* END BLOCK - TYPE */

				/* BEGIN BLOCK - Orgu Filter */
				$f->multiselectsearch
				(
					$txt("orgu_unit")
					, ""
					, $this->getOrgusFromTep()
				),
				/* END BLOCK - Orgu Filter */

				/* BEGIN BLOCK - VENUE */
				$f->multiselectsearch
				(
					$txt("venue")
					, ""

					, $this->changeArrKeys(gevOrgUnitUtils::getVenueNames())
				)
				/* END BLOCK - VENUE */

			)->map
				(
					function($date_period_predicate, $start, $end, $edu_program, $crs_title, $course_type, $orgu_unit, $venue)
					{
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"edu_program" => $edu_program
									,"crs_title" => $crs_title
									,"course_type" => $course_type
									,"orgu_unit" => $orgu_unit
									,"venue" => $venue);
					},
					$tf->dict
					(
						array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							 ,"start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"edu_program" => $tf->lst($tf->string())
							 ,"crs_title" => $tf->lst($tf->string())
							 ,"course_type" => $tf->lst($tf->string())
							 ,"orgu_unit" => $tf->lst($tf->int())
							 ,"venue" => $tf->lst($tf->string())
						)
					)
				)
		);
	}

	protected function getOrgusFromTep() {
		$orgus = array();
		$sql = "SELECT DISTINCT title, obj_id FROM object_data WHERE type = 'orgu'";
		$res = $this->gIldb->query($sql);
		while( $rec = $this->gIldb->fetchAssoc($res)) {
			$orgus[$rec["obj_id"]] = $rec["title"];
		}
		return $orgus;
	}

	protected function createTemplateFile() {
		$str = fopen($this->plugin->getDirectory()."/templates/default/"
			."tpl.trainer_op_by_tep_cat_row.html","w"); 
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td class = "bordered_right">{VAL_FULLNAME}</td>';
		foreach($this->categories as $key => $category) {
			$tpl .= "\n".'<td align = "right">{VAL_CAT_'.$key.'}</td>';
			$tpl .= "\n".'<td align = "right" class = "bordered_right">{VAL_CAT_'.$key.'_H}</td>';
		}
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}

	protected function getRowTemplateTitle() {
		return "tpl.trainer_op_by_tep_cat_row.html";
	}

	protected function changeArrKeys(array $arr) {
		$ret = array();

		foreach ($arr as $value) {
			$ret[$value] = $value;
		}
		return $ret;
	}
}