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

	protected function fetchData(callable $callback) {
		$db = $this->gIldb;
		$select = " SELECT `hu`.`user_id` ,CONCAT(hu.lastname, ', ', hu.firstname) as fullname ";
		$from = " FROM `hist_tep` ht ";

		foreach($this->categories as $key => $category) {
			$select .= $this->daysPerTEPCategory($category, 'cat_'.$key);
			$select .= $this->hoursPerTEPCategory($category, 'cat_'.$key.'_h');
		}

		$join .= "JOIN hist_user AS hu\n"
				."    ON ht.user_id = hu.user_id\n"
				."JOIN hist_tep_individ_days AS htid\n"
				."    ON individual_days = id\n"
				."JOIN hist_course AS hc\n"
				."    ON context_id = crs_id AND ht.category  = 'Training'\n";
		$where = " WHERE TRUE ";

		$filter = $this->filter();
		if($this->filter_settings) {//var_dump($this->filter_settings);exit;
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings[0]['period_pred']);
			$where .= "    AND " .$dt_query;

			if(!empty($settings['edu_program'])) {
				$where .= "    AND " .$db->in('hc.edu_program', $settings['edu_program'], false, 'text');
			}

			if(!empty($settings['crs_title'])) {
				$where .= "    AND " .$db->in('hc.title', $settings['crs_title'], false, 'text');
			}

			if(!empty($settings['course_type'])) {
				$where .= "    AND " .$db->in('hc_type', $settings['course_type'], false, 'text');
			}

			if(!empty($settings['venue'])) {
				$where .= "    AND " .$db->in('hc.venue', $settings['venue'], false, 'text');
			}
		}

		$group = "GROUP BY hu.user_id";
		$query = $select . $from . $join . $where . $group .$having .$order;//var_dump($query);exit;
		$res = $db->query($query);
		$data = [];

		while($rec = $db->fetchAssoc($res)) {
			$data[] = call_user_func($callback, $rec);
		}
		return $data;
	}

	protected function daysPerTEPCategory($category,$name) {
		$sql = ",SUM(IF(category = "
				.$this->gIldb->quote($category,"text")." ,1,0)) AS ".$name . " ";
		return $sql;
	}

	protected function hoursPerTEPCategory($category, $name) {
		$sql = 
		",SUM(IF(category = ".$this->gIldb->quote($category,"text")." ,"
		."		IF(htid.end_time IS NOT NULL AND htid.start_time IS NOT NULL,"
		."			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( end_time, start_time ) )* weight /720000) *2,8),"
		."			LEAST(CEIL( 28800* htid.weight /720000) *2,8)"
		."		)"
		."	,0)) AS ".$name . " ";
		return $sql;
	}

	protected function buildFilter($filter) {
		// $orgu_filter =  new recursiveOrguFilter('orgu_unit','ht.orgu_id',false,false);
		// $orgu_filter->setFilterOptionsAll();
		// $filter	->multiselect( "edu_program"
		// 					 , $this->plugin->txt("edu_program")
		// 					 , "hc.edu_program"
		// 					 , gevCourseUtils::getEduProgramsFromHisto()
		// 					 , array()
		// 					 , ""
		// 					 , 200
		// 					 , 160	
		// 					 )
		// 		->multiselect( "template_title"
		// 					 , $this->plugin->txt("crs_title")
		// 					 , "hc.template_title"
		// 					 , gevCourseUtils::getTemplateTitleFromHisto()
		// 					 , array()
		// 					 , ""
		// 					 , 300
		// 					 , 160	
		// 					 )
		// 		->multiselect( "type"
		// 					 , $this->plugin->txt("course_type")
		// 					 , "type"
		// 					 , gevCourseUtils::getLearningTypesFromHisto()
		// 					 , array()
		// 					 , ""
		// 					 , 200
		// 					 , 160	
		// 					 )
		// $orgu_filter->addToFilter($filter);
		// $filter	->multiselect( "venue"
		// 					 , $this->plugin->txt("venue")
		// 					 , "ht.location"
		// 					 , gevOrgUnitUtils::getVenueNames()
		// 					 , array()
		// 					 , ""
		// 					 , 300
		// 					 , 160
		// 					 )
		// 		->static_condition("(hc.hist_historic = 0 OR hc.hist_historic IS NULL)")
		// 		->static_condition("ht.hist_historic = 0")
		// 		->static_condition("ht.deleted = 0")
		// 		->static_condition("hu.hist_historic = 0")
		// 		->static_condition("(ht.category != 'Training' OR (ht.context_id != 0 AND ht.context_id IS NOT NULL))")
		// 		->static_condition($this->gIldb->in('ht.category',$this->categories,false,'text'))
		// 		->static_condition(' ht.row_id > '.self::MIN_ROW) 
		// 		->action($this->filter_action)
		// 		->compile();
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
					, gevCourseUtils::getEduProgramsFromHisto()
				)->map
					(
						function($types) { return $types; }
						,$tf->lst($tf->string())
					),
				/* END BLOCK - EDU PROGRAM */


				/* BEGIN BLOCK - TEMPLATE TITLE */
				$f->multiselectsearch
				(
					$txt("crs_title")
					, ""
					, gevCourseUtils::getTemplateTitleFromHisto()
				)->map
					(
						function($types) { return $types; }
						,$tf->lst($tf->string())
					),
				/* END BLOCK - TEMPLATE TITLE */


				/* BEGIN BLOCK - TYPE */
				$f->multiselectsearch
				(
					$txt("course_type")
					, ""
					, gevCourseUtils::getLearningTypesFromHisto()
				)->map
					(
						function($types) { return $types; }
						,$tf->lst($tf->string())
					),
				/* END BLOCK - TYPE */

				/* BEGIN BLOCK - VENUE */
				$f->multiselectsearch
				(
					$txt("venue")
					, ""
					, gevOrgUnitUtils::getVenueNames()
				)->map
					(
						function($types) { return $types; }
						,$tf->lst($tf->string())
					)
				/* END BLOCK - VENUE */

			)->map
				(
					function($date_period_predicate, $start, $end, $edu_program, $crs_title, $course_type, $venue)
					{
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"edu_program" => $edu_program
									,"crs_title" => $crs_title
									,"course_type" => $course_type
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
							 ,"venue" => $tf->lst($tf->string())
						)
					)
				)
		);
	}

	protected function getOrgusFromTep() {
		$orgus = array();
		$sql = "SELECT DISTINCT orgu_id FROM hist_tep WHERE orgu_title != '-empty-'";
		$res = $this->gIldb->query($sql);
		while( $rec = $this->gIldb->fetchAssoc($res)) {
			$orgus[] = $rec["orgu_title"];
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

}