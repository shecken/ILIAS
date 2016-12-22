<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrainerOpTrainerOrgu extends ilObjReportBase {
	const MIN_ROW = "3991";
	const shift = '<div class = "inline_block">&nbsp;&nbsp;</div>';
	protected $categories;
	protected $relevant_parameters = array();	


	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		global $tree;
		$this->tree = $tree;
	}

	public function initType() {
		 $this->setType("xoto");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_toto');
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_trainer_operation_by_orgu_and_trainer_row.html";
	}

	protected function buildTable($table) {
		$table->column("title", $this->plugin->txt("title"), true, "", false, false);
		foreach($this->meta_categories as $meta_category => $categories) {
			$table->column($meta_category."_d", $this->meta_category_names[$meta_category], true, "", false, false);
			$table->column($meta_category."_h", "Std.", true, "", false, false);
		}
		return parent::buildTable($table);
	}

	public function prepareReport() {
		include_once $this->plugin->getDirectory()."/config/cfg.trainer_operation_by_trainer_and_orgu.php";
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$this->meta_categories = $meta_categories;
		$this->meta_category_names = $meta_category_names;
		foreach ($top_orgus as $orgu_import_id) {
			$obj_id = ilObject::_getIdForImportId($orgu_import_id);
			if($obj_id !== null) {
				$this->top_nodes[$obj_id] = gevObjectUtils::getRefId($obj_id);
			}
		}
		parent::prepareReport();
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
					/* END BLOCK - PERIOD */


				/* BEGIN BLOCK - COURSE TUTOR */
				$f->multiselectsearch
				(
					$txt("crs_tutor")
					, ""
					, $this->getTEPTutors()
				),
				/* END BLOCK - COURSE TUTOR */


				/* BEGIN BLOCK - ORG UNIT */
				$f->multiselectsearch
				(
					$txt("org_unit_short")
					, ""
					, $this->getOrgusForFilter($this->top_nodes)
				)
				/* END BLOCK - ORG UNIT */

			)
		)->map
				(
					function($date_period_predicate, $start, $end, $crs_tutor, $org_unit_short)
					{
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"crs_tutor" => $crs_tutor
									,"org_unit_short" => $org_unit_short);
					},
					$tf->dict
					(
						array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							 ,"start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"crs_tutor" => $tf->lst($tf->int())
							 ,"org_unit_short" => $tf->lst($tf->int())
						)
					)
				);
	}

	protected function buildOrder($order) {
		return $order;
	}

	protected function buildQuery($query) {
		return null;
	}

	public function buildQueryStatement() {
		$db = $this->gIldb;
		$select = " SELECT ht.orgu_id,\n"
				 ." CONCAT(hu.lastname,', ',hu.firstname) as title";
		$from = " FROM hist_tep ht";
		$where = " WHERE hu.hist_historic = 0\n"
				."      AND (ht.category != 'Training' OR (ht.context_id != 0 AND ht.context_id IS NOT NULL))\n"
				."      AND ht.deleted = 0\n"
				."      AND ht.user_id != 0\n"
				."      AND ht.orgu_title != '-empty-'\n"
				."      AND ht.row_id > ".self::MIN_ROW ."\n";

		foreach($this->meta_categories as $meta_category => $categories) {
			$select .= "," .$this->daysPerTEPMetaCategory($categories, $meta_category."_d");
			$select .= "," .$this->hoursPerTEPMetaCategory($categories, $meta_category."_h");
		}

		$select .= " ,hu.user_id";

		$join = " JOIN hist_user hu\n"
			   ."     ON ht.user_id = hu.user_id\n"
			   ." JOIN hist_tep_individ_days AS htid\n"
			   ."     ON ht.individual_days = htid.id\n";

		$group = " GROUP BY ht.orgu_id, ht.user_id\n";

		$filter = $this->filter();
		if($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$this->orgu_filter = $settings['org_unit_short'];
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings['period_pred']);
			$where .= "    AND " .$dt_query;

			if(!empty($settings['crs_tutor'])) {
				$where .= "     AND " .$db->in('hu.user_id', $settings['crs_tutor'], false, "integer");
			}
		} else {
			$where .= "     AND ((`ht`.`begin_date` < '" .date("Y") ."-12-31' ) OR (`ht`.`begin_date` = '".date("Y") ."-12-31' ) ) AND (('".date("Y") ."-01-01' < `ht`.`begin_date` ) OR ('" .date("Y") ."-01-01' = `ht`.`begin_date` ) )\n";
		}

		$query = $select . $from . $join . $where . $group .$having .$order;
		return $query;
	}

	protected function fetchData(callable $callback) {
		$db = $this->gIldb;
		$query = $this->buildQueryStatement();
		$res = $db->query($query);
		$this->pre_data = array();
		while ($rec = $db->fetchAssoc($res)) {
			$this->pre_data[$rec["orgu_id"]][] = $rec;
		}

		if($this->orgu_filter) {
			$top_nodes = array();

			foreach ($this->orgu_filter as $orgu_id) {
				$top_nodes[$orgu_id] = gevObjectUtils::getRefId($orgu_id);
			}
		} else {
			$top_nodes = $this->top_nodes;
		}

		$top_sup_orgus = $this->getTopSuperiorNodesOfUser($top_nodes);
		$tree_data = array();

		foreach ($top_sup_orgus as $obj_id => $ref_id) {
			$tree_data[] = $this->buildReportTree($obj_id,$ref_id);
		}

		foreach($tree_data as $branch) {
			$this->fillData($branch, $callback);
		}

		return $this->report_data;
	}

	protected function daysPerTEPMetaCategory($categories, $name) {
		$sql = "SUM(IF(".$this->gIldb->in('category',$categories,false,"text")." ,1,0)) as ".$name;
		return $sql;
	}

	protected function hoursPerTEPMetaCategory($categories, $name) {
		$sql =
			"SUM(IF(".$this->gIldb->in('category',$categories,false,"text")." ,"
			."	IF(htid.end_time IS NOT NULL AND htid.start_time IS NOT NULL,"
			."			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time ) )* htid.weight /720000) *2,8),"
			."			LEAST(CEIL( 28800* htid.weight /720000) *2,8))"
			."	,0)) as ".$name;
		return $sql;
	}

	protected function arrayAddMetaCategories(array $factor1, array $factor2) {
		foreach($this->data_fields as $data_field) {
			$factor1[$meta_category] += $factor2[$meta_category];
		}
	}

	protected function getOrgusForFilter($below_orgus = null) {
		$get_obj_id = function($obj_ref_id) {return $obj_ref_id["obj_id"];};

		$all_sup_orgus = array_map($get_obj_id, $this->user_utils->getOrgUnitsWhereUserIsSuperior());
		if($below_orgus !== null) {
			$below_orgu_childs = array_map($get_obj_id, gevOrgUnitUtils::getAllChildren(array_unique(array_values($below_orgus))));
			$below_orgus = array_unique(array_merge(array_keys($below_orgus),$below_orgu_childs));
			$all_sup_orgus = array_intersect($below_orgus,$all_sup_orgus);
		}

		$return = array();
		foreach ($all_sup_orgus as $obj_id) {
			$return[$obj_id] =  ilObject::_lookupTitle($obj_id);
		}
		asort($return);
		return $return; //obj_id => title
	}

	protected function getTopSuperiorNodesOfUser($below_orgus = null) {
		$get_ref_id = function($obj_ref_id) {return $obj_ref_id["ref_id"];};
		$all_sup_orgus_ref = array_unique(array_map($get_ref_id
			, $this->user_utils->getOrgUnitsWhereUserIsSuperior()));

		if($below_orgus !== null) {
			$below_orgu_children = array();
			$below_orgu_children = array_map($get_ref_id,gevOrgUnitUtils::getAllChildren(array_unique($below_orgus)));
			$below_orgus = array_unique(array_merge($below_orgu_children,$below_orgus));
			$all_sup_orgus_ref = array_intersect($below_orgus,$all_sup_orgus_ref);
		}

		$sql = 	"SELECT obj_id, t1.child as ref_id, t2.child FROM tree t1 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = t1.child "
				."	LEFT JOIN tree t2 "
				." 		ON t1.lft > t2.lft AND t1.rgt < t2.rgt "
				."		AND ".$this->gIldb->in("t2.child",$all_sup_orgus_ref,false,"text")
				." WHERE ".$this->gIldb->in("t1.child",$all_sup_orgus_ref,false,"text")
				."		AND ore.deleted IS NULL "
				." HAVING t2.child IS NULL";

		$top_sup_orgus = array();
		$res = $this->gIldb->query($sql);

		while($rec = $this->gIldb->fetchAssoc($res)) {
			$top_sup_orgus[$rec["obj_id"]] = $rec["ref_id"];
		}
		return $top_sup_orgus; //obj_id => ref_id
	}

	protected function buildReportTree($obj_id,$ref_id,$offset = "") {
		$title = $this->pre_data[$obj_id][0]["orgu_title"] ?
			$this->pre_data[$obj_id][0]["orgu_title"] : ilObject::_lookupTitle($obj_id);
		$children = $this->tree->getChildsByType($ref_id,'orgu');
		$return = array("title"=>$title,"trainers"=>$this->pre_data[$obj_id],"children"=>array());

		foreach ($return["trainers"] as &$trainers) {
			$trainers["title"] = $offset.self::shift.'<div class = "inline_block">'.$trainers["title"].'</div>';
		}

		asort($return["trainers"]);

		foreach($children as $child) {
			$return["children"][] = $this->buildReportTree($child["obj_id"],$child["ref_id"],$offset.self::shift);
		}

		$return["sum"] = $this->sumMetaCategories($return["trainers"]);

		foreach ($return["children"] as $child_nr => $child) {
			$return["sum"] = $this->sumMetaCategories(array($return["sum"],$child["sum"]));
		}
		$return["sum"]["title"] = $offset.'<div class = "inline_block"><b>'.$return["title"].'</b></div>';
		return $return;
	}

	protected function  fillData($data_level, $callback) {
		$this->report_data[] = call_user_func($callback,$data_level["sum"]);
		foreach ($data_level["trainers"] as $values) {
			$this->report_data[] = call_user_func($callback, $values);
		}
		foreach ($data_level["children"] as $child) {
			$this->fillData($child, $callback);
		}
	}

	protected function sumMetaCategories ($arrays) {
		$return = array();
		foreach ($this->meta_categories as $meta_category => $categories) {
			$auxh = 0;
			$auxd = 0;
			foreach ($arrays as $array) {
				$auxh += $array[$meta_category."_h"];
				$auxd += $array[$meta_category."_d"];
			}
			$return[$meta_category."_h"] = $auxh;
			$return[$meta_category."_d"] = $auxd;
		}
		return $return;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function createTemplateFile() {
		$str = fopen($this->plugin->getDirectory()."/templates/default/"
			."tpl.gev_trainer_operation_by_orgu_and_trainer_row.html","w");
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td class = "bordered_right" style= "white-space:nowrap">{VAL_TITLE}</td>';
		foreach($this->meta_categories as $meta_category => $categories) {
			$tpl .= "\n".'<td align = "right">{VAL_'.strtoupper($meta_category).'_D}</td>';
			$tpl .= "\n".'<td align = "right" class = "bordered_right">{VAL_'.strtoupper($meta_category).'_H}</td>';
			$i++;
		}
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}

	protected function getTEPTutors() {
		$sql = 	"SELECT hu.user_id ,CONCAT(hu.lastname,', ',hu.firstname) as fullname FROM hist_tep ht \n"
				."	JOIN hist_user hu ON hu.user_id = ht.user_id \n"
				."	WHERE ht.hist_historic = 0 AND hu.hist_historic = 0"
				."		AND ht.row_id > ".$this->gIldb->quote(self::MIN_ROW)
				."	GROUP BY ht.user_id";
		$res = $this->gIldb->query($sql);
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec["user_id"]] = $rec["fullname"];
		}
		asort($return);
		return $return;
	}
}