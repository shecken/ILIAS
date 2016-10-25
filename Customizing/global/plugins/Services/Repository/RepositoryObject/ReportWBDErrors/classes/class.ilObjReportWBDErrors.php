<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportWBDErrors extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $gCtrl;
	public $filter_settings;

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		global $ilCtrl,$lng;
		$this->gCtrl = $ilCtrl;
		$this->gLng = $lng;
		$this->filter_settings = null;
		$this->id_counter = 1;
	}

	public function initType() {
		$this->setType("xwbe");
	}
	
	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_wbe');
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_wbd_errors_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildOrder($order) {
		return $order;
	}

	protected function buildTable($table) {
		$table	->column("ts", $this->plugin->txt("ts"), true)
				->column("action", $this->plugin->txt("wbd_errors_action"), true)
				->column("internal", $this->plugin->txt( "wbd_errors_internal"), true)
				->column("status", $this->plugin->txt( "status"), true)
				->column("user_id", $this->plugin->txt("usr_id"), true)
				->column("course_id", $this->plugin->txt("crs_id"), true)
				->column("firstname", $this->plugin->txt("firstname"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("title", $this->plugin->txt("title"), true)
				->column("begin_date", $this->plugin->txt("begin_date"), true)
				->column("end_date", $this->plugin->txt("end_date"), true)
				->column("reason",$this->plugin->txt( "wbd_errors_reason"), true)
				->column("reason_full", $this->plugin->txt("wbd_errors_reason_full"), true)
				->column("resolve", $this->plugin->txt("actions"), 1, 0, 1);
		return parent::buildTable($table);
	}

	protected function buildQuery($query) {
		return $query;
	}

	// TODO: Those are not really used, as we use the new filter logic
	// in this report. Remove em!
	protected function buildFilter($filter) {
		return null;
	}

	public function deliverFilter() {
		return null;
	}
	//
	// As is don't use a regular filter, i also don't need its params...
	protected function addFilterToRelevantParameters() {
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->plugin->txt($id); };

		return $f->sequence(
					$f->sequence(
						$f->dateperiod
							( $txt("error_dateperiod")
							, ""
							)->map(function($start,$end) use ($f) {
									$pc = $f->dateperiod_overlaps_predicate
										( "err.ts"
										, "err.ts"
										);
									return array("date_period_predicate" => $pc($start,$end)
										,"start" => $start
										,"end" => $end);
									},$tf->dict(array(
										"date_period_predicate" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
										,"start" => $tf->cls("DateTime")
										,"end" => $tf->cls("DateTime")
									)))
						,$f->multiselectsearch
								( $txt("reason")
								, ""
								, $this->getFilterValues('reason', 'wbd_errors')
							)->map(function($id_s) {return array_values($id_s);}
							,$tf->lst($tf->string()))
						,
						$f->multiselectsearch
							( $txt("action")
							, ""
							, $this->getFilterValues('action', 'wbd_errors')
						)->map(function($id_s) {return $id_s;}
							,$tf->lst($tf->string()))
						,
						$f->multiselectsearch
							( $txt("error_type")
							, ""
							, $this->getErrorFilterValues()
						)->map(function($id_s) {return $id_s;}
							,$tf->lst($tf->int()))
						,$f->multiselectsearch
							( $txt("status")
							, ""
							, $this->getStatusFilterValues()
						)->default_choice($this->getStatusFilterDefaults())
						 ->map(function($id_s) {return array_values($id_s);}
						,$tf->lst($tf->string()))
					)->map(function($date_period_predicate, $start, $end, $reason,$action,$error_type,$status) {
						return array("period_pred" => $date_period_predicate
							, "start" => $start
							, "end" => $end
							, "reason" => $reason
							, "action" => $action
							, "error_type" => $error_type
							, "status" => $status
							);}
						, $tf->dict(array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							,"start" => $tf->cls("DateTime")
							,"end" => $tf->cls("DateTime")
							,"reason" => $tf->lst($tf->string())
							,"action" => $tf->lst($tf->string())
							,"error_type" => $tf->lst($tf->int())
							,"status" => $tf->lst($tf->string())))
				));
	}

	public function fetchData(callable $callback) {
		/**
		 *	The following is not nice. I'll have to think of a better way to postprocess data from database, than the static transformResultRow.
		 *	It probably would suffice simply to make is nonstatic...
		 */
		$db = $this->gIldb;
		$query = "SELECT GROUP_CONCAT(DISTINCT err.id SEPARATOR ',') as ids, err.usr_id, err.crs_id, err.internal, err.reason, err.status, GROUP_CONCAT(DISTINCT err.reason_full SEPARATOR ',') as reason_full, DATE(err.ts) as err_date, err.action, usr.firstname, usr.lastname\n"
				.", crs.title, usrcrs.begin_date, usrcrs.end_date\n"
				." FROM wbd_errors err\n"
				." LEFT JOIN hist_user usr ON err.usr_id = usr.user_id\n"
				."     AND usr.hist_historic = 0\n"
				." LEFT JOIN hist_course crs ON err.crs_id = crs.crs_id\n"
				."    AND crs.hist_historic = 0\n"
				." LEFT JOIN hist_usercoursestatus usrcrs ON err.usr_id = usrcrs.usr_id\n"
				."    AND err.crs_id = usrcrs.crs_id\n"
				."    AND usrcrs.hist_historic = 0\n"
				." LEFT JOIN usr_data ud ON err.usr_id = ud.usr_id";

		$filter = $this->filter();

		if($this->filter_settings) {


			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings[0]["period_pred"]);
			$query .= " WHERE ".$dt_query;

			if(!empty($settings[0]["reason"])) {
				$query .= "    AND ".$db->in("err.reason", $settings[0]["reason"], false, "text");
			}

			if(!empty($settings[0]["action"])) {
				$query .= "    AND ".$db->in("err.action", $settings[0]["action"], false, "text");
			}

			if(!empty($settings[0]["error_type"])) {
				$query .= "    AND ".$db->in("err.internal", $settings[0]["error_type"], false, "text");
			}

			if(!empty($settings[0]["status"])) {
				$query .= "    AND ".$db->in("err.status", $settings[0]["status"], false, "text");
			}
		} else {
			$year = date("y");
			$query .= " WHERE ((`err`.`ts` < '".$year."-12-31' ) OR (`err`.`ts` = '".$year."-12-31' ) ) AND (('".$year."-01-01' < `err`.`ts` ) OR ('".$year."-01-01' = `err`.`ts` ) )";
			$query .= "     AND err.status IN ('feedback','not_resolved')";
		}

		$query .= " GROUP BY usr_id, crs_id, internal, reason, err_date, action, firstname, lastname, status";

		$res = $db->query($query);
		$data = array();
		while($rec = $db->fetchAssoc($res)) {
			$link_change_usr = $this->gCtrl->getLinkTargetByClass(
				array("iladministrationgui", "ilobjusergui"), "edit")
				.'&obj_id='.$rec['usr_id']
				.'&ref_id=7'; //ref 7: Manage user accounts here.
			$link_usr = '<a href="' .$link_change_usr.'">%s</a>';

			foreach (array('usr_id','firstname','lastname') as $key) {
				$rec[$key] = sprintf($link_usr, $rec[$key]);
			}

			$crs_ref_id = gevObjectUtils::getRefId($rec['crs_id']);
			if($crs_ref_id && $rec['crs_id'] > 0){
				$link_change_crs = $this->gCtrl->getLinkTargetByClass(
					array("ilrepositorygui", "ilobjcoursegui"), "editInfo")
					.'&ref_id='
					.$crs_ref_id;
				$link_change_crs = '<a href="' .$link_change_crs.'">%s</a>';
			} else {
				$link_change_crs = '%s';
			}
			$rec['crs_id'] = sprintf($link_change_crs, $rec['crs_id']);

			$rec['resolve'] = $this->getActionMenu(base64_encode($rec['ids']));

			$reasons = explode(",", $rec["reason_full"]);
			$new_reasons = array();
			foreach ($reasons as $key => $reason) {
				if($this->gLng->exists($reason)) {
					$new_reasons[] = $this->gLng->txt($reason);
				} else {
					$new_reasons[] = $reason;
				}
			}

			$rec["reason_full"] = implode("<br />", $new_reasons);

			if((int)$rec["internal"] === 1) {
				$rec["internal"] = $this->plugin->txt("internal");
			} else if ((int)$rec["internal"] === 0) {
				$rec["internal"] = $this->plugin->txt("wbd");
			}

			$rec["ts"] = $rec["err_date"];

			$rec["status"] = $this->plugin->txt($rec["status"]);

			$data[] = $rec;
		}

		return $data;
	}

	protected function getFilterValues($column, $table) {
		$ret = array();
		$query = "SELECT DISTINCT ".$column." FROM ".$table."";

		$res = $this->gIldb->query($query);
		while($row = $this->gIldb->fetchAssoc($res)) {
			$ret[$row[$column]] = $row[$column];
		}

		return $ret;
	}

	protected function getStatusFilterValues() {
		return array("not_resolved" => $this->plugin->txt("not_resolved")
				   , "resolved" => $this->plugin->txt("resolve")
				   , "feedback" => $this->plugin->txt("feedback")
				   , "unable_resolve" => $this->plugin->txt("unable_resolve")
			);
	}

	protected function getStatusFilterDefaults() {
		return array("not_resolved"
				   , "feedback"
			);
	}

	protected function getErrorFilterValues() {
		return array( 1 => $this->plugin->txt("internal")
					, 0 => $this->plugin->txt("wbd")
					);
	}

	protected function getActionMenu($err_ids) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new \ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->txt("actions"));
		$current_selection_list->setId($this->id_counter);
		$current_selection_list->setSelectionHeaderClass("small");
		$current_selection_list->setItemLinkClass("xsmall");
		$current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$current_selection_list->setUseImages(false);
		$current_selection_list->setAdditionalToggleElement("err_ids".$err_ids, "ilContainerListItemOuterHighlight");

		foreach ($this->getActionMenuItems($err_ids) as $key => $value) {
			$current_selection_list->addItem($value["title"],"",$value["link"],$value["image"],"",$value["frame"]);
		}
		$this->id_counter ++;
		return $current_selection_list->getHTML();
	}

	protected function getActionMenuItems($err_ids) {
		$this->gCtrl->setParameterByClass("ilObjReportWBDErrorsGUI", "err_ids", $err_ids);
		$link_resolve = $this->memberlist_link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportWBDErrorsGUI"), "resolve");
		$link_feedback = $this->memberlist_link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportWBDErrorsGUI"), "feedback");
		$link_unable_resolve = $this->memberlist_link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportWBDErrorsGUI"), "unableResolve");
		$this->gCtrl->clearParametersByClass("ilObjReportWBDErrorsGUI");

		$items = array();
		$items[] = array("title" => $this->plugin->txt("resolve"), "link" => $link_resolve, "image" => "", "frame"=>"");
		$items[] = array("title" => $this->plugin->txt("feedback"), "link" => $link_feedback, "image" => "", "frame"=>"");
		$items[] = array("title" => $this->plugin->txt("unable_resolve"), "link" => $link_unable_resolve, "image" => "", "frame"=>"");

		return $items;
	}
}
